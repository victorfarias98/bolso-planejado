import { computed, reactive, ref } from 'vue';
import { http } from '../api/http';
import { sortRows } from '../utils/tableSort';

export function useInvestments(toast) {
    const items = ref([]);
    const loading = ref(true);
    const saving = ref(false);
    const deleting = ref(false);
    const formError = ref('');
    const sortKey = ref('created_at');
    const sortDir = ref('desc');

    const metaTotals = ref(null);
    const metaAnalysis = ref(null);

    const formModalOpen = ref(false);
    const bulkModalOpen = ref(false);
    const deleteModalOpen = ref(false);
    const editingId = ref(null);
    const selectedIds = ref([]);
    const deleteTargetIds = ref([]);
    const deleteTargetLabel = ref('');

    const investmentTypes = [
        { value: 'pocket', label: 'Caixinha' },
        { value: 'cdb', label: 'CDB' },
        { value: 'treasury', label: 'Tesouro' },
        { value: 'fund', label: 'Fundo' },
        { value: 'stocks', label: 'Ações' },
        { value: 'crypto', label: 'Cripto' },
        { value: 'other', label: 'Outro' },
    ];

    const form = reactive({
        title: '',
        investment_type: 'pocket',
        current_amount: '',
        monthly_contribution: '0',
        monthly_return_rate: '0.50',
        contribution_day: 5,
        target_amount: '',
        notes: '',
        is_active: true,
    });

    const bulkRows = ref([createBulkRow(), createBulkRow(), createBulkRow()]);
    const modalTitle = computed(() => (editingId.value ? 'Editar investimento' : 'Novo investimento'));
    const selectedCount = computed(() => selectedIds.value.length);
    const allVisibleSelected = computed(() =>
        displayedItems.value.length > 0 && displayedItems.value.every((i) => selectedIds.value.includes(i.id)),
    );

    const displayedItems = computed(() =>
        sortRows(items.value, sortKey.value, sortDir.value, {
            title: (r) => r.title ?? '',
            current_amount: (r) => Number(r.current_amount),
            monthly_contribution: (r) => Number(r.monthly_contribution),
            monthly_return_rate: (r) => Number(r.monthly_return_rate),
            projected_12m: (r) => Number(r.projected_12m),
            created_at: (r) => r.created_at ?? '',
        }),
    );

    function createBulkRow() {
        return {
            title: '',
            investment_type: 'pocket',
            current_amount: '',
            monthly_contribution: '0',
            monthly_return_rate: '0.50',
        };
    }

    function onSort(column) {
        if (sortKey.value === column) {
            sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortKey.value = column;
            sortDir.value = 'desc';
        }
    }

    function clearForm() {
        editingId.value = null;
        form.title = '';
        form.investment_type = 'pocket';
        form.current_amount = '';
        form.monthly_contribution = '0';
        form.monthly_return_rate = '0.50';
        form.contribution_day = 5;
        form.target_amount = '';
        form.notes = '';
        form.is_active = true;
        formError.value = '';
    }

    function abrirNovo() {
        clearForm();
        formModalOpen.value = true;
    }

    function abrirEdicao(item) {
        editingId.value = item.id;
        form.title = item.title;
        form.investment_type = item.investment_type;
        form.current_amount = String(item.current_amount);
        form.monthly_contribution = String(item.monthly_contribution ?? '0');
        form.monthly_return_rate = String(item.monthly_return_rate ?? '0');
        form.contribution_day = Number(item.contribution_day ?? 5);
        form.target_amount = item.target_amount != null ? String(item.target_amount) : '';
        form.notes = item.notes ?? '';
        form.is_active = !!item.is_active;
        formModalOpen.value = true;
    }

    async function carregar() {
        loading.value = true;
        try {
            const { data } = await http.get('/investments');
            items.value = data.data ?? [];
            metaTotals.value = data.meta?.totals ?? null;
            metaAnalysis.value = data.meta?.analysis ?? null;
            const visible = new Set(items.value.map((i) => i.id));
            selectedIds.value = selectedIds.value.filter((id) => visible.has(id));
        } finally {
            loading.value = false;
        }
    }

    async function salvar() {
        formError.value = '';
        saving.value = true;
        try {
            const payload = {
                title: form.title,
                investment_type: form.investment_type,
                current_amount: form.current_amount || '0',
                monthly_contribution: form.monthly_contribution || '0',
                monthly_return_rate: form.monthly_return_rate || '0',
                contribution_day: form.contribution_day || 5,
                target_amount: form.target_amount || null,
                notes: form.notes?.trim() || null,
                is_active: !!form.is_active,
            };
            if (editingId.value) {
                await http.patch(`/investments/${editingId.value}`, payload);
                toast.success('Investimento atualizado.');
            } else {
                await http.post('/investments', payload);
                toast.success('Investimento cadastrado.');
            }
            formModalOpen.value = false;
            clearForm();
            await carregar();
        } catch (e) {
            const errs = e.response?.data?.errors;
            formError.value = errs ? Object.values(errs).flat().join(' ') : 'Não foi possível salvar.';
        } finally {
            saving.value = false;
        }
    }

    function abrirExclusao(item) {
        deleteTargetIds.value = [item.id];
        deleteTargetLabel.value = item.title;
        deleteModalOpen.value = true;
    }

    function abrirExclusaoSelecionadas() {
        if (!selectedIds.value.length) return;
        deleteTargetIds.value = [...selectedIds.value];
        deleteTargetLabel.value = `${selectedIds.value.length} investimentos selecionados`;
        deleteModalOpen.value = true;
    }

    async function confirmarExclusao() {
        if (!deleteTargetIds.value.length) return;
        deleting.value = true;
        try {
            await Promise.all(deleteTargetIds.value.map((id) => http.delete(`/investments/${id}`)));
            selectedIds.value = selectedIds.value.filter((id) => !deleteTargetIds.value.includes(id));
            deleteModalOpen.value = false;
            deleteTargetIds.value = [];
            deleteTargetLabel.value = '';
            toast.success('Investimento(s) removido(s).');
            await carregar();
        } catch {
            toast.error('Não foi possível excluir.');
        } finally {
            deleting.value = false;
        }
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
        const rows = bulkRows.value.filter((r) => r.title.trim() && r.current_amount);
        if (!rows.length) {
            formError.value = 'Preencha ao menos uma linha com nome e valor atual.';
            return;
        }
        saving.value = true;
        try {
            await Promise.all(rows.map((r) => http.post('/investments', {
                title: r.title.trim(),
                investment_type: r.investment_type,
                current_amount: r.current_amount,
                monthly_contribution: r.monthly_contribution || '0',
                monthly_return_rate: r.monthly_return_rate || '0',
            })));
            toast.success(`${rows.length} investimentos criados.`);
            bulkModalOpen.value = false;
            await carregar();
        } catch (e) {
            const errs = e.response?.data?.errors;
            formError.value = errs ? Object.values(errs).flat().join(' ') : 'Não foi possível salvar em massa.';
        } finally {
            saving.value = false;
        }
    }

    function toggleSelection(id) {
        if (selectedIds.value.includes(id)) {
            selectedIds.value = selectedIds.value.filter((x) => x !== id);
        } else {
            selectedIds.value = [...selectedIds.value, id];
        }
    }

    function toggleSelectAllVisible() {
        if (allVisibleSelected.value) {
            const visible = new Set(displayedItems.value.map((i) => i.id));
            selectedIds.value = selectedIds.value.filter((id) => !visible.has(id));
            return;
        }
        const next = new Set(selectedIds.value);
        for (const i of displayedItems.value) next.add(i.id);
        selectedIds.value = [...next];
    }

    return {
        loading,
        saving,
        deleting,
        formError,
        sortKey,
        sortDir,
        displayedItems,
        metaTotals,
        metaAnalysis,
        formModalOpen,
        bulkModalOpen,
        deleteModalOpen,
        selectedIds,
        selectedCount,
        allVisibleSelected,
        deleteTargetLabel,
        modalTitle,
        investmentTypes,
        form,
        bulkRows,
        onSort,
        abrirNovo,
        abrirEdicao,
        salvar,
        abrirExclusao,
        abrirExclusaoSelecionadas,
        confirmarExclusao,
        abrirAdicaoEmMassa,
        addBulkRow,
        removeBulkRow,
        salvarEmMassa,
        toggleSelection,
        toggleSelectAllVisible,
        carregar,
    };
}
