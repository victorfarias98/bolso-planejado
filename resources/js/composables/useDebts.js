import { computed, reactive, ref } from 'vue';
import { http } from '../api/http';

export function useDebts(toast) {
    const accounts = ref([]);
    const items = ref([]);
    const totals = ref(null);
    const loading = ref(true);
    const saving = ref(false);
    const deleting = ref(false);
    const formError = ref('');
    const editingId = ref(null);

    const formModalOpen = ref(false);
    const bulkModalOpen = ref(false);
    const deleteModalOpen = ref(false);
    const deleteTargetIds = ref([]);
    const deleteTargetTitle = ref('');
    const selectedIds = ref([]);

    const modalTitle = computed(() => (editingId.value ? 'Editar dívida' : 'Nova dívida'));
    const selectedCount = computed(() => selectedIds.value.length);
    const allVisibleSelected = computed(() =>
        items.value.length > 0 && items.value.every((d) => selectedIds.value.includes(d.id)),
    );

    const debtTypes = [
        { value: 'card', label: 'Cartão' },
        { value: 'loan', label: 'Empréstimo' },
        { value: 'personal', label: 'Pessoal' },
        { value: 'store', label: 'Loja / carnê' },
        { value: 'family', label: 'Familiar' },
        { value: 'other', label: 'Outro' },
    ];

    const debtStatuses = [
        { value: 'open', label: 'Em aberto' },
        { value: 'negotiation', label: 'Em negociação' },
        { value: 'agreement_active', label: 'Acordo ativo' },
        { value: 'paid_off', label: 'Quitada' },
    ];

    const form = reactive({
        title: '',
        creditor: '',
        debt_type: 'card',
        status: 'open',
        principal_amount: '',
        balance_amount: '',
        financial_account_id: '',
        agreement_formalized_on: '',
        agreement_end_on: '',
        agreement_first_due_date: '',
        agreement_installment_count: null,
        agreement_installment_amount: '',
        agreement_down_payment: '',
        agreement_notes: '',
        sync_recurrence: false,
    });

    const bulkRows = ref([
        createEmptyBulkRow(),
        createEmptyBulkRow(),
        createEmptyBulkRow(),
    ]);

    function createEmptyBulkRow() {
        return {
            title: '',
            principal_amount: '',
            balance_amount: '',
            creditor: '',
            debt_type: 'card',
            status: 'open',
        };
    }

    function clearForm() {
        editingId.value = null;
        form.title = '';
        form.creditor = '';
        form.debt_type = 'card';
        form.status = 'open';
        form.principal_amount = '';
        form.balance_amount = '';
        form.financial_account_id = '';
        form.agreement_formalized_on = '';
        form.agreement_end_on = '';
        form.agreement_first_due_date = '';
        form.agreement_installment_count = null;
        form.agreement_installment_amount = '';
        form.agreement_down_payment = '';
        form.agreement_notes = '';
        form.sync_recurrence = false;
        formError.value = '';
    }

    function fecharFormulario() {
        clearForm();
        formModalOpen.value = false;
    }

    function onFormModalClose() {
        clearForm();
    }

    function abrirNova() {
        clearForm();
        formModalOpen.value = true;
    }

    function preencherForm(d) {
        form.title = d.title;
        form.creditor = d.creditor ?? '';
        form.debt_type = d.debt_type;
        form.status = d.status;
        form.principal_amount = String(d.principal_amount);
        form.balance_amount = String(d.balance_amount);
        form.financial_account_id = d.financial_account_id ?? '';
        form.agreement_formalized_on = d.agreement_formalized_on ? String(d.agreement_formalized_on).slice(0, 10) : '';
        form.agreement_end_on = d.agreement_end_on ? String(d.agreement_end_on).slice(0, 10) : '';
        form.agreement_first_due_date = d.agreement_first_due_date ? String(d.agreement_first_due_date).slice(0, 10) : '';
        form.agreement_installment_count = d.agreement_installment_count ?? null;
        form.agreement_installment_amount = d.agreement_installment_amount != null ? String(d.agreement_installment_amount) : '';
        form.agreement_down_payment = d.agreement_down_payment != null ? String(d.agreement_down_payment) : '';
        form.agreement_notes = d.agreement_notes ?? '';
        form.sync_recurrence = false;
        formError.value = '';
    }

    function abrirEdicao(d) {
        clearForm();
        editingId.value = d.id;
        preencherForm(d);
        formModalOpen.value = true;
    }

    function abrirExclusao(d) {
        deleteTargetIds.value = [d.id];
        deleteTargetTitle.value = d.creditor ? `${d.title} (${d.creditor})` : d.title;
        deleteModalOpen.value = true;
    }

    function abrirExclusaoSelecionadas() {
        if (!selectedIds.value.length) return;
        deleteTargetIds.value = [...selectedIds.value];
        deleteTargetTitle.value = `${selectedIds.value.length} dívidas selecionadas`;
        deleteModalOpen.value = true;
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
            const visible = new Set(items.value.map((d) => d.id));
            selectedIds.value = selectedIds.value.filter((id) => !visible.has(id));
            return;
        }
        const next = new Set(selectedIds.value);
        for (const d of items.value) next.add(d.id);
        selectedIds.value = [...next];
    }

    function resetBulkRows() {
        bulkRows.value = [createEmptyBulkRow(), createEmptyBulkRow(), createEmptyBulkRow()];
    }

    function abrirAdicaoEmMassa() {
        resetBulkRows();
        formError.value = '';
        bulkModalOpen.value = true;
    }

    function addBulkRow() {
        bulkRows.value.push(createEmptyBulkRow());
    }

    function removeBulkRow(index) {
        if (bulkRows.value.length <= 1) return;
        bulkRows.value.splice(index, 1);
    }

    async function salvarEmMassa() {
        const rows = bulkRows.value
            .map((r) => ({
                ...r,
                title: r.title.trim(),
                creditor: r.creditor.trim(),
            }))
            .filter((r) => r.title && r.principal_amount);

        if (!rows.length) {
            formError.value = 'Preencha ao menos uma linha com título e valor principal.';
            return;
        }

        saving.value = true;
        formError.value = '';
        try {
            await Promise.all(
                rows.map((r) =>
                    http.post('/debts', {
                        title: r.title,
                        principal_amount: r.principal_amount,
                        balance_amount: r.balance_amount || null,
                        creditor: r.creditor || null,
                        debt_type: r.debt_type,
                        status: r.status,
                    }),
                ),
            );
            toast.success(`${rows.length} dívidas adicionadas.`);
            bulkModalOpen.value = false;
            resetBulkRows();
            await carregar();
        } catch (e) {
            const errs = e.response?.data?.errors;
            formError.value = errs ? Object.values(errs).flat().join(' ') : 'Não foi possível adicionar em massa.';
        } finally {
            saving.value = false;
        }
    }

    async function carregarContas() {
        const { data } = await http.get('/financial-accounts');
        accounts.value = data.data ?? [];
    }

    async function carregar() {
        loading.value = true;
        try {
            const { data } = await http.get('/debts');
            items.value = data.data ?? [];
            totals.value = data.meta?.totals ?? null;
            const visible = new Set(items.value.map((d) => d.id));
            selectedIds.value = selectedIds.value.filter((id) => visible.has(id));
        } finally {
            loading.value = false;
        }
    }

    function buildPayload() {
        const isEdit = !!editingId.value;
        if (isEdit) {
            return {
                title: form.title,
                creditor: form.creditor?.trim() || null,
                debt_type: form.debt_type,
                status: form.status,
                principal_amount: form.principal_amount,
                balance_amount: form.balance_amount === '' ? '0' : form.balance_amount,
                financial_account_id: form.financial_account_id || null,
                agreement_formalized_on: form.agreement_formalized_on || null,
                agreement_end_on: form.agreement_end_on || null,
                agreement_first_due_date: form.agreement_first_due_date || null,
                agreement_installment_count: form.agreement_installment_count ?? null,
                agreement_installment_amount: form.agreement_installment_amount === '' ? null : form.agreement_installment_amount,
                agreement_down_payment: form.agreement_down_payment === '' ? null : form.agreement_down_payment,
                agreement_notes: form.agreement_notes?.trim() || null,
            };
        }

        const payload = {
            title: form.title,
            creditor: form.creditor?.trim() || null,
            debt_type: form.debt_type,
            status: form.status,
            principal_amount: form.principal_amount,
        };
        if (form.balance_amount !== '' && form.balance_amount != null) payload.balance_amount = form.balance_amount;
        if (form.financial_account_id) payload.financial_account_id = form.financial_account_id;
        if (form.agreement_formalized_on) payload.agreement_formalized_on = form.agreement_formalized_on;
        if (form.agreement_end_on) payload.agreement_end_on = form.agreement_end_on;
        if (form.agreement_first_due_date) payload.agreement_first_due_date = form.agreement_first_due_date;
        if (form.agreement_installment_count) payload.agreement_installment_count = form.agreement_installment_count;
        if (form.agreement_installment_amount) payload.agreement_installment_amount = form.agreement_installment_amount;
        if (form.agreement_down_payment) payload.agreement_down_payment = form.agreement_down_payment;
        if (form.agreement_notes?.trim()) payload.agreement_notes = form.agreement_notes.trim();
        if (form.sync_recurrence) payload.sync_recurrence = true;
        return payload;
    }

    async function salvar() {
        formError.value = '';

        if (form.sync_recurrence && !form.financial_account_id) {
            formError.value = 'Selecione a conta para lançar as saídas das parcelas do acordo.';
            return;
        }

        saving.value = true;
        try {
            if (editingId.value) {
                await http.patch(`/debts/${editingId.value}`, buildPayload());
                toast.success('Dívida atualizada.');
            } else {
                await http.post('/debts', buildPayload());
                toast.success(form.sync_recurrence ? 'Dívida criada com recorrência na previsão.' : 'Dívida cadastrada.');
            }
            await carregar();
            fecharFormulario();
        } catch (e) {
            const errs = e.response?.data?.errors;
            formError.value = errs ? Object.values(errs).flat().join(' ') : 'Não foi possível salvar.';
        } finally {
            saving.value = false;
        }
    }

    async function confirmarExclusao() {
        if (!deleteTargetIds.value.length) return;
        const ids = [...deleteTargetIds.value];
        deleting.value = true;
        try {
            await Promise.all(ids.map((id) => http.delete(`/debts/${id}`)));
            toast.success(ids.length === 1 ? 'Dívida removida.' : `${ids.length} dívidas removidas.`);
            deleteModalOpen.value = false;
            deleteTargetIds.value = [];
            deleteTargetTitle.value = '';
            if (ids.includes(editingId.value) && formModalOpen.value) fecharFormulario();
            selectedIds.value = selectedIds.value.filter((id) => !ids.includes(id));
            await carregar();
        } catch {
            toast.error('Não foi possível excluir.');
        } finally {
            deleting.value = false;
        }
    }

    async function init() {
        await carregarContas();
        await carregar();
    }

    return {
        accounts,
        items,
        totals,
        loading,
        saving,
        deleting,
        formError,
        editingId,
        formModalOpen,
        bulkModalOpen,
        deleteModalOpen,
        deleteTargetTitle,
        modalTitle,
        selectedIds,
        selectedCount,
        allVisibleSelected,
        debtTypes,
        debtStatuses,
        form,
        bulkRows,
        init,
        abrirNova,
        abrirAdicaoEmMassa,
        abrirEdicao,
        abrirExclusao,
        abrirExclusaoSelecionadas,
        toggleSelection,
        toggleSelectAllVisible,
        addBulkRow,
        removeBulkRow,
        fecharFormulario,
        onFormModalClose,
        salvar,
        salvarEmMassa,
        confirmarExclusao,
    };
}
