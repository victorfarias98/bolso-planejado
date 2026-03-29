import { computed, reactive, ref } from 'vue';
import { http } from '../api/http';
import { sortRows } from '../utils/tableSort';

export function useAccounts(toast) {
    const accounts = ref([]);
    const sortKey = ref('created_at');
    const sortDir = ref('desc');
    const loading = ref(true);
    const saving = ref(false);
    const savingEdit = ref(false);
    const deleting = ref(false);
    const formError = ref('');
    const editingId = ref(null);
    const formModalOpen = ref(false);
    const editModalOpen = ref(false);
    const deleteModalOpen = ref(false);
    const bulkModalOpen = ref(false);
    const selectedIds = ref([]);
    const deleteTargetIds = ref([]);
    const deleteTargetLabel = ref('');

    const editDraft = reactive({
        name: '',
        initial_balance: '0',
        currency: 'BRL',
    });

    const form = reactive({
        name: '',
        initial_balance: '0',
    });
    const bulkRows = ref([createBulkRow(), createBulkRow(), createBulkRow()]);

    const selectedCount = computed(() => selectedIds.value.length);
    const allVisibleSelected = computed(() =>
        displayedAccounts.value.length > 0 && displayedAccounts.value.every((a) => selectedIds.value.includes(a.id)),
    );

    function createBulkRow() {
        return {
            name: '',
            initial_balance: '0',
            currency: 'BRL',
        };
    }

    const displayedAccounts = computed(() =>
        sortRows(accounts.value, sortKey.value, sortDir.value, {
            initial_balance: (r) => Number(r.initial_balance),
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
            const { data } = await http.get('/financial-accounts');
            accounts.value = data.data ?? [];
            const visible = new Set(accounts.value.map((a) => a.id));
            selectedIds.value = selectedIds.value.filter((id) => visible.has(id));
        } finally {
            loading.value = false;
        }
    }

    function iniciarEdicao(a) {
        editingId.value = a.id;
        editDraft.name = a.name;
        editDraft.initial_balance = String(a.initial_balance);
        editDraft.currency = a.currency || 'BRL';
        editModalOpen.value = true;
    }

    function cancelarEdicao() {
        editingId.value = null;
        editModalOpen.value = false;
    }

    async function salvarEdicao(id) {
        savingEdit.value = true;
        try {
            await http.patch(`/financial-accounts/${id}`, {
                name: editDraft.name,
                initial_balance: editDraft.initial_balance,
                currency: editDraft.currency,
            });
            editingId.value = null;
            editModalOpen.value = false;
            toast.success('Conta atualizada.');
            await carregar();
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Não foi possível salvar.');
        } finally {
            savingEdit.value = false;
        }
    }

    async function criar() {
        formError.value = '';
        saving.value = true;
        try {
            await http.post('/financial-accounts', {
                name: form.name,
                initial_balance: form.initial_balance,
            });
            form.name = '';
            form.initial_balance = '0';
            formModalOpen.value = false;
            toast.success('Conta criada.');
            await carregar();
        } catch (e) {
            formError.value = e.response?.data?.message ?? 'Erro ao salvar.';
        } finally {
            saving.value = false;
        }
    }

    async function remover(id) {
        deleteTargetIds.value = [id];
        deleteTargetLabel.value = '1 conta selecionada';
        deleteModalOpen.value = true;
    }

    async function confirmarExclusao() {
        if (!deleteTargetIds.value.length) return;
        deleting.value = true;
        try {
            await Promise.all(deleteTargetIds.value.map((id) => http.delete(`/financial-accounts/${id}`)));
            if (deleteTargetIds.value.includes(editingId.value)) {
                editingId.value = null;
                editModalOpen.value = false;
            }
            selectedIds.value = selectedIds.value.filter((id) => !deleteTargetIds.value.includes(id));
            toast.success(deleteTargetIds.value.length === 1 ? 'Conta removida.' : `${deleteTargetIds.value.length} contas removidas.`);
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
        const rows = bulkRows.value.filter((r) => r.name.trim());
        if (!rows.length) {
            formError.value = 'Preencha ao menos uma linha com nome.';
            return;
        }
        saving.value = true;
        try {
            await Promise.all(
                rows.map((r) =>
                    http.post('/financial-accounts', {
                        name: r.name.trim(),
                        initial_balance: r.initial_balance || '0',
                        currency: r.currency || 'BRL',
                    }),
                ),
            );
            toast.success(`${rows.length} contas criadas.`);
            bulkModalOpen.value = false;
            await carregar();
        } catch (e) {
            formError.value = e.response?.data?.message ?? 'Erro ao salvar em massa.';
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
            const visible = new Set(displayedAccounts.value.map((a) => a.id));
            selectedIds.value = selectedIds.value.filter((id) => !visible.has(id));
            return;
        }
        const next = new Set(selectedIds.value);
        for (const a of displayedAccounts.value) next.add(a.id);
        selectedIds.value = [...next];
    }

    function abrirExclusaoSelecionadas() {
        if (!selectedIds.value.length) return;
        deleteTargetIds.value = [...selectedIds.value];
        deleteTargetLabel.value = `${selectedIds.value.length} contas selecionadas`;
        deleteModalOpen.value = true;
    }

    return {
        accounts,
        sortKey,
        sortDir,
        displayedAccounts,
        onSort,
        loading,
        saving,
        savingEdit,
        deleting,
        formError,
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
        editDraft,
        form,
        carregar,
        abrirNova,
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
    };
}
