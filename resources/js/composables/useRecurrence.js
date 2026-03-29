import { computed, reactive, ref } from 'vue';
import { createCategory } from '../api/categories';
import { http } from '../api/http';
import { sortRows } from '../utils/tableSort';

export function useRecurrence(toast) {
    const accounts = ref([]);
    const categories = ref([]);
    const series = ref([]);
    const sortKey = ref('created_at');
    const sortDir = ref('desc');
    const loading = ref(true);
    const saving = ref(false);
    const deleting = ref(false);
    const formError = ref('');
    const editError = ref('');
    const editingId = ref(null);
    const formModalOpen = ref(false);
    const editModalOpen = ref(false);
    const deleteModalOpen = ref(false);
    const bulkModalOpen = ref(false);
    const selectedIds = ref([]);
    const deleteTargetIds = ref([]);
    const deleteTargetLabel = ref('');
    const deleteHasLinkedDebt = ref(false);

    const form = reactive({
        financial_account_id: '',
        category_id: '',
        type: 'income',
        amount: '',
        day_of_month: 5,
        start_on: new Date().toISOString().slice(0, 10),
        end_on: '',
        max_occurrences: null,
        description: '',
    });
    const editDraft = reactive({
        financial_account_id: '',
        category_id: '',
        type: 'income',
        amount: '',
        day_of_month: 5,
        start_on: '',
        end_on: '',
        max_occurrences: null,
        description: '',
        is_active: true,
    });
    const bulkRows = ref([createBulkRow(), createBulkRow(), createBulkRow()]);

    const newCategoryModalOpen = ref(false);
    const newCategoryName = ref('');
    const creatingCategory = ref(false);
    const newCategoryTarget = ref(null);

    const selectedCount = computed(() => selectedIds.value.length);
    const allVisibleSelected = computed(() =>
        displayedSeries.value.length > 0 && displayedSeries.value.every((r) => selectedIds.value.includes(r.id)),
    );

    function createBulkRow() {
        return {
            financial_account_id: '',
            type: 'expense',
            amount: '',
            day_of_month: 5,
            start_on: new Date().toISOString().slice(0, 10),
            description: '',
        };
    }

    const displayedSeries = computed(() =>
        sortRows(series.value, sortKey.value, sortDir.value, {
            description: (r) => r.description ?? '',
            day_of_month: (r) => Number(r.day_of_month),
            amount: (r) => Number(r.amount),
            type: (r) => r.type ?? '',
            start_on: (r) => r.start_on ?? '',
            is_active: (r) => (r.is_active ? 1 : 0),
            created_at: (r) => r.created_at ?? '',
        }),
    );

    function onSort(column) {
        if (sortKey.value === column) {
            sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortKey.value = column;
            sortDir.value = 'desc';
        }
    }

    async function carregar() {
        loading.value = true;
        try {
            const [acc, cat, rec] = await Promise.all([
                http.get('/financial-accounts'),
                http.get('/categories'),
                http.get('/recurrence-series'),
            ]);
            accounts.value = acc.data.data ?? [];
            categories.value = cat.data.data ?? [];
            series.value = rec.data.data ?? [];
            const visible = new Set(series.value.map((r) => r.id));
            selectedIds.value = selectedIds.value.filter((id) => visible.has(id));
        } finally {
            loading.value = false;
        }
    }

    async function carregarCategorias() {
        const { data } = await http.get('/categories');
        categories.value = data.data ?? [];
    }

    function abrirNovaCategoria(target) {
        newCategoryTarget.value = target;
        newCategoryName.value = '';
        newCategoryModalOpen.value = true;
    }

    async function salvarNovaCategoria() {
        const name = newCategoryName.value.trim();
        if (!name) {
            toast.error('Informe o nome da categoria.');
            return;
        }
        creatingCategory.value = true;
        try {
            const created = await createCategory({ name });
            await carregarCategorias();
            const t = newCategoryTarget.value;
            if (t) {
                t.category_id = created.id;
            }
            newCategoryName.value = '';
            newCategoryModalOpen.value = false;
            toast.success('Categoria criada.');
        } catch (e) {
            const errs = e.response?.data?.errors;
            const msg = errs
                ? Object.values(errs).flat().join(' ')
                : e.response?.data?.message || 'Não foi possível criar a categoria.';
            toast.error(msg);
        } finally {
            creatingCategory.value = false;
        }
    }

    async function criar() {
        formError.value = '';
        saving.value = true;
        try {
            const payload = {
                financial_account_id: form.financial_account_id,
                type: form.type,
                amount: form.amount,
                day_of_month: form.day_of_month,
                start_on: form.start_on,
                description: form.description || null,
                is_active: true,
            };
            if (form.category_id) payload.category_id = form.category_id;
            if (form.end_on) payload.end_on = form.end_on;
            if (form.max_occurrences) payload.max_occurrences = form.max_occurrences;

            await http.post('/recurrence-series', payload);
            form.amount = '';
            form.description = '';
            formModalOpen.value = false;
            toast.success('Recorrência criada.');
            await carregar();
        } catch (e) {
            const errs = e.response?.data?.errors;
            formError.value = errs ? Object.values(errs).flat().join(' ') : 'Erro ao salvar.';
        } finally {
            saving.value = false;
        }
    }

    async function toggleActive(r) {
        try {
            await http.patch(`/recurrence-series/${r.id}`, { is_active: !r.is_active });
            toast.info(r.is_active ? 'Recorrência pausada.' : 'Recorrência reativada.');
            await carregar();
        } catch {
            toast.error('Não foi possível atualizar.');
        }
    }

    async function remover(id) {
        const row = series.value.find((r) => r.id === id);
        deleteTargetIds.value = [id];
        deleteTargetLabel.value = '1 recorrência selecionada';
        deleteHasLinkedDebt.value = !!row?.has_linked_debt;
        deleteModalOpen.value = true;
    }

    function iniciarEdicao(r) {
        editingId.value = r.id;
        editError.value = '';
        editDraft.financial_account_id = r.financial_account_id;
        editDraft.category_id = r.category_id ?? '';
        editDraft.type = r.type;
        editDraft.amount = String(r.amount);
        editDraft.day_of_month = Number(r.day_of_month);
        editDraft.start_on = String(r.start_on).slice(0, 10);
        editDraft.end_on = r.end_on ? String(r.end_on).slice(0, 10) : '';
        editDraft.max_occurrences = r.max_occurrences ?? null;
        editDraft.description = r.description ?? '';
        editDraft.is_active = !!r.is_active;
        editModalOpen.value = true;
    }

    function cancelarEdicao() {
        editingId.value = null;
        editError.value = '';
        editModalOpen.value = false;
    }

    async function salvarEdicao() {
        if (!editingId.value) return;
        try {
            const payload = {
                financial_account_id: editDraft.financial_account_id,
                type: editDraft.type,
                amount: editDraft.amount,
                day_of_month: editDraft.day_of_month,
                start_on: editDraft.start_on,
                end_on: editDraft.end_on || null,
                max_occurrences: editDraft.max_occurrences || null,
                description: editDraft.description || null,
                is_active: !!editDraft.is_active,
            };
            payload.category_id = editDraft.category_id || null;
            await http.patch(`/recurrence-series/${editingId.value}`, payload);
            toast.success('Recorrência atualizada.');
            editModalOpen.value = false;
            editingId.value = null;
            await carregar();
        } catch (e) {
            const errs = e.response?.data?.errors;
            editError.value = errs ? Object.values(errs).flat().join(' ') : 'Não foi possível salvar.';
        }
    }

    async function confirmarExclusao() {
        if (!deleteTargetIds.value.length) return;
        deleting.value = true;
        try {
            await Promise.all(deleteTargetIds.value.map((id) => http.delete(`/recurrence-series/${id}`)));
            if (deleteTargetIds.value.includes(editingId.value)) {
                editingId.value = null;
                editModalOpen.value = false;
            }
            selectedIds.value = selectedIds.value.filter((id) => !deleteTargetIds.value.includes(id));
            toast.success(deleteTargetIds.value.length === 1 ? 'Recorrência removida.' : `${deleteTargetIds.value.length} recorrências removidas.`);
            deleteModalOpen.value = false;
            deleteTargetIds.value = [];
            deleteTargetLabel.value = '';
            deleteHasLinkedDebt.value = false;
            await carregar();
        } catch {
            toast.error('Não foi possível excluir.');
        } finally {
            deleting.value = false;
        }
    }

    function abrirNova() {
        formError.value = '';
        formModalOpen.value = true;
    }

    function abrirAdicaoEmMassa() {
        bulkRows.value = [createBulkRow(), createBulkRow(), createBulkRow()];
        formError.value = '';
        bulkModalOpen.value = true;
    }

    function addBulkRow() {
        bulkRows.value.push(createBulkRow());
    }

    function removeBulkRow(index) {
        if (bulkRows.value.length <= 1) return;
        bulkRows.value.splice(index, 1);
    }

    async function salvarEmMassa() {
        const rows = bulkRows.value.filter((r) => r.financial_account_id && r.amount && r.start_on);
        if (!rows.length) {
            formError.value = 'Preencha ao menos uma linha com conta, valor e início.';
            return;
        }
        saving.value = true;
        formError.value = '';
        try {
            await Promise.all(
                rows.map((r) =>
                    http.post('/recurrence-series', {
                        financial_account_id: r.financial_account_id,
                        type: r.type,
                        amount: r.amount,
                        day_of_month: r.day_of_month,
                        start_on: r.start_on,
                        description: r.description || null,
                        is_active: true,
                    }),
                ),
            );
            toast.success(`${rows.length} recorrências criadas.`);
            bulkModalOpen.value = false;
            await carregar();
        } catch (e) {
            const errs = e.response?.data?.errors;
            formError.value = errs ? Object.values(errs).flat().join(' ') : 'Erro ao salvar em massa.';
        } finally {
            saving.value = false;
        }
    }

    function toggleSelection(id) {
        if (selectedIds.value.includes(id)) {
            selectedIds.value = selectedIds.value.filter((x) => x !== id);
            return;
        }
        selectedIds.value = [...selectedIds.value, id];
    }

    function toggleSelectAllVisible() {
        if (allVisibleSelected.value) {
            const visible = new Set(displayedSeries.value.map((r) => r.id));
            selectedIds.value = selectedIds.value.filter((id) => !visible.has(id));
            return;
        }
        const next = new Set(selectedIds.value);
        for (const r of displayedSeries.value) next.add(r.id);
        selectedIds.value = [...next];
    }

    function abrirExclusaoSelecionadas() {
        if (!selectedIds.value.length) return;
        deleteTargetIds.value = [...selectedIds.value];
        deleteTargetLabel.value = `${selectedIds.value.length} recorrências selecionadas`;
        deleteHasLinkedDebt.value = series.value.some(
            (r) => deleteTargetIds.value.includes(r.id) && r.has_linked_debt,
        );
        deleteModalOpen.value = true;
    }

    return {
        accounts,
        categories,
        series,
        sortKey,
        sortDir,
        displayedSeries,
        onSort,
        loading,
        saving,
        deleting,
        formError,
        editError,
        editingId,
        formModalOpen,
        editModalOpen,
        deleteModalOpen,
        bulkModalOpen,
        selectedIds,
        selectedCount,
        allVisibleSelected,
        bulkRows,
        deleteTargetLabel,
        deleteHasLinkedDebt,
        newCategoryModalOpen,
        newCategoryName,
        creatingCategory,
        abrirNovaCategoria,
        salvarNovaCategoria,
        form,
        editDraft,
        carregar,
        abrirNova,
        abrirAdicaoEmMassa,
        addBulkRow,
        removeBulkRow,
        salvarEmMassa,
        toggleSelection,
        toggleSelectAllVisible,
        abrirExclusaoSelecionadas,
        criar,
        toggleActive,
        iniciarEdicao,
        cancelarEdicao,
        salvarEdicao,
        remover,
        confirmarExclusao,
    };
}
