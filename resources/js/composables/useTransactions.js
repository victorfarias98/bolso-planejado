import { computed, reactive, ref } from 'vue';
import { createCategory } from '../api/categories';
import { http } from '../api/http';

export function useTransactions(toast) {
    function monthToRange(month) {
        const [y, m] = String(month).split('-').map(Number);
        const start = new Date(y, (m || 1) - 1, 1);
        const end = new Date(y, (m || 1), 0);
        const toIso = (d) => d.toISOString().slice(0, 10);
        return { from: toIso(start), to: toIso(end) };
    }

    function shiftMonth(month, delta) {
        const [y, m] = String(month).split('-').map(Number);
        const dt = new Date(y, ((m || 1) - 1) + delta, 1);
        const yy = dt.getFullYear();
        const mm = String(dt.getMonth() + 1).padStart(2, '0');
        return `${yy}-${mm}`;
    }

    function currentMonth() {
        const now = new Date();
        return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
    }

    const accounts = ref([]);
    const categories = ref([]);
    const items = ref([]);
    const meta = ref({ current_page: 1, last_page: 1 });
    const loading = ref(true);
    const saving = ref(false);
    const savingEdit = ref(false);
    const deleting = ref(false);
    const exportingPdf = ref(false);
    async function baixarRelatorioPdf() {
        exportingPdf.value = true;
        try {
            const { data } = await http.get('/reports/monthly', {
                params: { month: month.value },
                responseType: 'blob',
            });
            const blob = new Blob([data], { type: 'application/pdf' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `relatorio-mensal-${month.value}.pdf`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        } catch {
            toast.error('Não foi possível gerar o PDF.');
        } finally {
            exportingPdf.value = false;
        }
    }

    const formError = ref('');
    const editError = ref('');
    const editingId = ref(null);
    const sortKey = ref('created_at');
    const sortDir = ref('desc');
    const month = ref(currentMonth());

    const filters = reactive({
        financial_account_id: '',
        status: '',
    });
    const formModalOpen = ref(false);
    const editModalOpen = ref(false);
    const deleteModalOpen = ref(false);
    const bulkModalOpen = ref(false);
    const selectedIds = ref([]);
    const deleteTargetIds = ref([]);
    const deleteTargetLabel = ref('');

    const sortHint = computed(() => {
        const dirPt = sortDir.value === 'desc' ? 'decrescente' : 'crescente';
        const map = {
            created_at: `cadastro (${dirPt})`,
            occurred_on: `data do lançamento (${dirPt})`,
            description: `descrição (${dirPt})`,
            account_name: `nome da conta (${dirPt})`,
            type: `tipo (${dirPt})`,
            amount: `valor (${dirPt})`,
            status: `status (${dirPt})`,
        };
        return map[sortKey.value] ?? `cadastro (${dirPt})`;
    });

    const form = reactive({
        financial_account_id: '',
        category_id: '',
        type: 'expense',
        amount: '',
        occurred_on: new Date().toISOString().slice(0, 10),
        status: 'scheduled',
        description: '',
    });

    const editDraft = reactive({
        financial_account_id: '',
        category_id: '',
        type: 'expense',
        amount: '',
        occurred_on: '',
        status: 'scheduled',
        description: '',
    });

    const bulkRows = ref([createBulkRow(), createBulkRow(), createBulkRow()]);

    const newCategoryModalOpen = ref(false);
    const newCategoryName = ref('');
    const creatingCategory = ref(false);
    const newCategoryTarget = ref(null);

    const selectedCount = computed(() => selectedIds.value.length);
    const allVisibleSelected = computed(() =>
        items.value.length > 0 && items.value.every((t) => selectedIds.value.includes(t.id)),
    );

    function createBulkRow() {
        return {
            financial_account_id: '',
            category_id: '',
            type: 'expense',
            amount: '',
            occurred_on: new Date().toISOString().slice(0, 10),
            status: 'scheduled',
            description: '',
        };
    }

    async function carregarCats() {
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
            await carregarCats();
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

    async function carregarContas() {
        const { data } = await http.get('/financial-accounts');
        accounts.value = data.data ?? [];
    }

    async function carregar() {
        loading.value = true;
        try {
            const params = {
                page: meta.value.current_page,
                per_page: 20,
                sort: sortKey.value,
                direction: sortDir.value,
            };
            if (filters.financial_account_id) params.financial_account_id = filters.financial_account_id;
            if (filters.status) params.status = filters.status;
            const range = monthToRange(month.value);
            params.from = range.from;
            params.to = range.to;

            const { data } = await http.get('/transactions', { params });
            items.value = data.data ?? [];
            meta.value = {
                current_page: data.meta?.current_page ?? 1,
                last_page: data.meta?.last_page ?? 1,
            };
            const visible = new Set(items.value.map((t) => t.id));
            selectedIds.value = selectedIds.value.filter((id) => visible.has(id));
        } finally {
            loading.value = false;
        }
    }

    function onSort(column) {
        if (sortKey.value === column) {
            sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortKey.value = column;
            sortDir.value = 'desc';
        }
        meta.value.current_page = 1;
        carregar();
    }

    function prevMonth() {
        month.value = shiftMonth(month.value, -1);
        meta.value.current_page = 1;
        carregar();
    }

    function nextMonth() {
        month.value = shiftMonth(month.value, 1);
        meta.value.current_page = 1;
        carregar();
    }

    function setMonth(value) {
        month.value = value || currentMonth();
        meta.value.current_page = 1;
        carregar();
    }

    function page(p) {
        meta.value.current_page = p;
        editingId.value = null;
        carregar();
    }

    function iniciarEdicao(t) {
        editingId.value = t.id;
        editError.value = '';
        editDraft.financial_account_id = t.financial_account_id;
        editDraft.category_id = t.category_id ?? '';
        editDraft.type = t.type;
        editDraft.amount = String(t.amount);
        editDraft.occurred_on = String(t.occurred_on).slice(0, 10);
        editDraft.status = t.status;
        editDraft.description = t.description ?? '';
        editModalOpen.value = true;
    }

    function cancelarEdicao() {
        editingId.value = null;
        editError.value = '';
        editModalOpen.value = false;
    }

    async function salvarEdicao() {
        if (!editingId.value) return;
        savingEdit.value = true;
        editError.value = '';
        try {
            const payload = {
                financial_account_id: editDraft.financial_account_id,
                type: editDraft.type,
                amount: editDraft.amount,
                occurred_on: editDraft.occurred_on,
                status: editDraft.status,
                description: editDraft.description || null,
            };
            payload.category_id = editDraft.category_id || null;
            await http.put(`/transactions/${editingId.value}`, payload);
            editingId.value = null;
            editModalOpen.value = false;
            toast.success('Transação atualizada.');
            await carregar();
        } catch (e) {
            const errs = e.response?.data?.errors;
            editError.value = errs ? Object.values(errs).flat().join(' ') : 'Erro ao salvar.';
        } finally {
            savingEdit.value = false;
        }
    }

    async function criar() {
        formError.value = '';
        saving.value = true;
        try {
            const payload = { ...form };
            if (!payload.category_id) delete payload.category_id;
            await http.post('/transactions', payload);
            form.amount = '';
            form.description = '';
            meta.value.current_page = 1;
            formModalOpen.value = false;
            toast.success('Lançamento criado.');
            await carregar();
        } catch (e) {
            const errs = e.response?.data?.errors;
            formError.value = errs ? Object.values(errs).flat().join(' ') : 'Erro ao salvar.';
        } finally {
            saving.value = false;
        }
    }

    async function remover(id) {
        deleteTargetIds.value = [id];
        deleteTargetLabel.value = '1 lançamento selecionado';
        deleteModalOpen.value = true;
    }

    async function confirmarExclusao() {
        if (!deleteTargetIds.value.length) return;
        deleting.value = true;
        try {
            await Promise.all(deleteTargetIds.value.map((id) => http.delete(`/transactions/${id}`)));
            if (deleteTargetIds.value.includes(editingId.value)) {
                editingId.value = null;
                editModalOpen.value = false;
            }
            selectedIds.value = selectedIds.value.filter((id) => !deleteTargetIds.value.includes(id));
            toast.success(deleteTargetIds.value.length === 1 ? 'Lançamento removido.' : `${deleteTargetIds.value.length} lançamentos removidos.`);
            deleteModalOpen.value = false;
            deleteTargetIds.value = [];
            deleteTargetLabel.value = '';
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
        const rows = bulkRows.value.filter((r) => r.financial_account_id && r.amount && r.occurred_on);
        if (!rows.length) {
            formError.value = 'Preencha ao menos uma linha com conta, valor e data.';
            return;
        }
        saving.value = true;
        formError.value = '';
        try {
            await Promise.all(
                rows.map((r) => {
                    const payload = {
                        financial_account_id: r.financial_account_id,
                        type: r.type,
                        amount: r.amount,
                        occurred_on: r.occurred_on,
                        status: r.status,
                        description: r.description || null,
                    };
                    if (r.category_id) payload.category_id = r.category_id;
                    return http.post('/transactions', payload);
                }),
            );
            toast.success(`${rows.length} lançamentos criados.`);
            bulkModalOpen.value = false;
            meta.value.current_page = 1;
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
            const visible = new Set(items.value.map((t) => t.id));
            selectedIds.value = selectedIds.value.filter((id) => !visible.has(id));
            return;
        }
        const next = new Set(selectedIds.value);
        for (const t of items.value) next.add(t.id);
        selectedIds.value = [...next];
    }

    function abrirExclusaoSelecionadas() {
        if (!selectedIds.value.length) return;
        deleteTargetIds.value = [...selectedIds.value];
        deleteTargetLabel.value = `${selectedIds.value.length} lançamentos selecionados`;
        deleteModalOpen.value = true;
    }

    async function init() {
        await Promise.all([carregarContas(), carregarCats()]);
        await carregar();
    }

    return {
        accounts,
        categories,
        items,
        meta,
        loading,
        saving,
        savingEdit,
        deleting,
        exportingPdf,
        formError,
        editError,
        editingId,
        sortKey,
        sortDir,
        month,
        filters,
        sortHint,
        form,
        editDraft,
        formModalOpen,
        editModalOpen,
        deleteModalOpen,
        bulkModalOpen,
        selectedIds,
        selectedCount,
        allVisibleSelected,
        bulkRows,
        deleteTargetLabel,
        newCategoryModalOpen,
        newCategoryName,
        creatingCategory,
        abrirNovaCategoria,
        salvarNovaCategoria,
        init,
        onSort,
        prevMonth,
        nextMonth,
        setMonth,
        page,
        abrirNova,
        baixarRelatorioPdf,
        abrirAdicaoEmMassa,
        addBulkRow,
        removeBulkRow,
        salvarEmMassa,
        toggleSelection,
        toggleSelectAllVisible,
        abrirExclusaoSelecionadas,
        iniciarEdicao,
        cancelarEdicao,
        salvarEdicao,
        criar,
        remover,
        confirmarExclusao,
        carregar,
    };
}
