<template>
    <div>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-white">
                    Dívidas e acordos
                </h1>
                <p class="mt-1 text-slate-400">
                    Valor total, situação do acordo e opção de gerar parcelas na previsão (recorrência mensal).
                </p>
            </div>
            <button
                type="button"
                class="dz-btn dz-btn-primary shrink-0 px-4 text-sm font-medium"
                @click="abrirNova"
            >
                Nova dívida
            </button>
            <button
                type="button"
                class="dz-btn dz-btn-ghost shrink-0 px-4 text-sm font-medium"
                @click="abrirAdicaoEmMassa"
            >
                Adição em massa
            </button>
        </div>
        <button
            type="button"
            class="dz-fab md:hidden"
            aria-label="Nova dívida"
            @click="abrirNova"
        >
            +
        </button>

        <BulkSelectionBar
            v-if="selectedCount"
            :selected-count="selectedCount"
            singular-label="dívida selecionada"
            plural-label="dívidas selecionadas"
            action-label="Excluir selecionadas"
            @action="abrirExclusaoSelecionadas"
        />

        <div
            v-if="totals"
            class="mt-6 grid gap-3 sm:grid-cols-3"
        >
            <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4">
                <p class="text-xs uppercase text-slate-500">
                    Saldo restante (ativas)
                </p>
                <p class="mt-1 text-xl font-semibold text-rose-300">
                    {{ formatBrl(totals.balance_total) }}
                </p>
            </div>
            <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4">
                <p class="text-xs uppercase text-slate-500">
                    Principal (ativas)
                </p>
                <p class="mt-1 text-xl font-semibold text-white">
                    {{ formatBrl(totals.principal_total) }}
                </p>
            </div>
            <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4">
                <p class="text-xs uppercase text-slate-500">
                    Cadastros
                </p>
                <p class="mt-1 text-xl font-semibold text-slate-200">
                    {{ totals.count }}
                </p>
            </div>
        </div>

        <div class="mt-8 space-y-3 md:hidden">
            <template v-if="loading">
                <div class="dz-skeleton h-24 w-full rounded-xl" />
                <div class="dz-skeleton h-24 w-full rounded-xl" />
            </template>
            <template v-else>
                <div
                    v-for="d in items"
                    :key="d.id"
                    class="dz-mobile-card rounded-xl border border-slate-800 bg-slate-900/40 p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-white">{{ d.title }}</p>
                            <p v-if="d.creditor" class="mt-1 text-xs text-slate-500">{{ d.creditor }}</p>
                        </div>
                        <input
                            type="checkbox"
                            :checked="selectedIds.includes(d.id)"
                            class="rounded border-slate-600 bg-slate-950 text-emerald-500"
                            @change="toggleSelection(d.id)"
                        >
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <p class="text-slate-400">Saldo</p>
                        <p class="text-right text-rose-200">{{ formatBrl(d.balance_amount) }}</p>
                        <p class="text-slate-400">Principal</p>
                        <p class="text-right text-slate-300">{{ formatBrl(d.principal_amount) }}</p>
                        <p class="text-slate-400">Status</p>
                        <p class="text-right text-slate-300">{{ labelStatus(d.status) }}</p>
                        <p class="text-slate-400">Acordo</p>
                        <p class="text-right" :class="d.agreement_is_finalized ? 'text-emerald-300' : 'text-amber-300'">{{ d.agreement_is_finalized ? 'Finalizado' : 'Vigente' }}</p>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button type="button" class="dz-btn dz-btn-ghost flex-1 text-xs" @click="abrirEdicao(d)">Editar</button>
                        <button type="button" class="dz-btn flex-1 border border-rose-800/60 text-xs text-rose-300 hover:bg-rose-950/30" @click="abrirExclusao(d)">Excluir</button>
                    </div>
                </div>
                <div v-if="!items.length" class="rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-8 text-center text-slate-500">
                    Nenhuma dívida cadastrada.
                </div>
            </template>
        </div>

        <div class="mt-8 hidden overflow-x-auto rounded-xl border border-slate-800 md:block">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-800 bg-slate-900/60 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">
                            <input
                                type="checkbox"
                                :checked="allVisibleSelected"
                                class="rounded border-slate-600 bg-slate-950 text-emerald-500"
                                @change="toggleSelectAllVisible"
                            >
                        </th>
                        <th class="px-4 py-3">
                            Título
                        </th>
                        <th class="px-4 py-3">
                            Saldo
                        </th>
                        <th class="px-4 py-3">
                            Principal
                        </th>
                        <th class="px-4 py-3">
                            Status
                        </th>
                        <th class="px-4 py-3">
                            Acordo
                        </th>
                        <th class="px-4 py-3">
                            Parcelas
                        </th>
                        <th class="px-4 py-3">
                            Recorrência ativa
                        </th>
                        <th class="px-4 py-3 text-right">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td
                            colspan="9"
                            class="dz-loading-pulse px-4 py-10 text-center text-slate-500"
                        >
                            Carregando…
                        </td>
                    </tr>
                    <template v-else>
                        <tr
                            v-for="d in items"
                            :key="d.id"
                            class="border-b border-slate-800/80 hover:bg-slate-800/20"
                        >
                            <td class="px-4 py-3">
                                <input
                                    type="checkbox"
                                    :checked="selectedIds.includes(d.id)"
                                    class="rounded border-slate-600 bg-slate-950 text-emerald-500"
                                    @change="toggleSelection(d.id)"
                                >
                            </td>
                            <td class="px-4 py-3 font-medium text-white">
                                {{ d.title }}
                                <span
                                    v-if="d.creditor"
                                    class="block text-xs font-normal text-slate-500"
                                >{{ d.creditor }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-rose-200">
                                {{ formatBrl(d.balance_amount) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-400">
                                {{ formatBrl(d.principal_amount) }}
                            </td>
                            <td class="px-4 py-3 text-slate-400">
                                {{ labelStatus(d.status) }}
                            </td>
                            <td class="px-4 py-3">
                                <span :class="d.agreement_is_finalized ? 'text-emerald-400' : 'text-amber-400'">
                                    {{ d.agreement_is_finalized ? 'Finalizado' : 'Vigente' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400">
                                <template v-if="d.agreement_installment_count">
                                    {{ d.agreement_installment_count }}× {{ formatBrl(d.agreement_installment_amount) }}
                                </template>
                                <template v-else>
                                    —
                                </template>
                            </td>
                            <td class="px-4 py-3 text-slate-400">
                                {{ d.has_recurrence ? 'Sim' : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <button
                                    type="button"
                                    class="mr-3 text-xs font-medium text-emerald-400 hover:text-emerald-300"
                                    @click="abrirEdicao(d)"
                                >
                                    Editar
                                </button>
                                <button
                                    type="button"
                                    class="text-xs font-medium text-rose-400 hover:text-rose-300"
                                    @click="abrirExclusao(d)"
                                >
                                    Excluir
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!items.length">
                            <td
                                colspan="9"
                                class="px-4 py-8 text-center text-slate-500"
                            >
                                Nenhuma dívida cadastrada.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Formulário criar / editar -->
        <Modal
            v-model:show="formModalOpen"
            :title="modalTitle"
            @close="onFormModalClose"
        >
            <form
                id="form-divida"
                class="grid gap-3 lg:grid-cols-2"
                @submit.prevent="salvar"
            >
                <div>
                    <label class="text-xs text-slate-500">Título</label>
                    <input
                        v-model="form.title"
                        required
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                        placeholder="Ex.: Cartão Nubank"
                    >
                </div>
                <div>
                    <label class="text-xs text-slate-500">Credor (opcional)</label>
                    <input
                        v-model="form.creditor"
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                    >
                </div>
                <div>
                    <label class="text-xs text-slate-500">Tipo</label>
                    <select
                        v-model="form.debt_type"
                        required
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                    >
                        <option
                            v-for="t in debtTypes"
                            :key="t.value"
                            :value="t.value"
                        >
                            {{ t.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-500">Status</label>
                    <select
                        v-model="form.status"
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                    >
                        <option
                            v-for="s in debtStatuses"
                            :key="s.value"
                            :value="s.value"
                        >
                            {{ s.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-500">Valor principal (total original)</label>
                    <input
                        v-model="form.principal_amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        required
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                    >
                </div>
                <div>
                    <label class="text-xs text-slate-500">Saldo restante</label>
                    <input
                        v-model="form.balance_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                        placeholder="Igual ao principal se vazio (novo)"
                    >
                </div>
                <div>
                    <label class="text-xs text-slate-500">Conta para parcelas (acordo) <span v-if="form.sync_recurrence" class="text-rose-300">*</span></label>
                    <select
                        v-model="form.financial_account_id"
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                    >
                        <option value="">
                            —
                        </option>
                        <option
                            v-for="a in accounts"
                            :key="a.id"
                            :value="a.id"
                        >
                            {{ a.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-500">Data da formalização</label>
                    <DzDatePicker
                        v-model="form.agreement_formalized_on"
                        class="mt-1 w-full"
                        input-class="w-full"
                        :min-date="FORM_DATE_MIN"
                        :max-date="FORM_DATE_MAX"
                    />
                </div>
                <div>
                    <label class="text-xs text-slate-500">Data de finalização</label>
                    <DzDatePicker
                        v-model="form.agreement_end_on"
                        class="mt-1 w-full"
                        input-class="w-full"
                        :min-date="FORM_DATE_MIN"
                        :max-date="FORM_DATE_MAX"
                    />
                </div>
                <div class="border-t border-slate-800 pt-4 lg:col-span-2">
                    <p class="text-xs font-medium uppercase text-slate-500">
                        Parcelamento do acordo
                    </p>
                </div>
                <div>
                    <label class="text-xs text-slate-500">Primeira parcela (data)</label>
                    <DzDatePicker
                        v-model="form.agreement_first_due_date"
                        class="mt-1 w-full"
                        input-class="w-full"
                        :min-date="FORM_DATE_MIN"
                        :max-date="FORM_DATE_MAX"
                    />
                </div>
                <div>
                    <label class="text-xs text-slate-500">Qtd. parcelas</label>
                    <input
                        v-model.number="form.agreement_installment_count"
                        type="number"
                        min="1"
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                    >
                </div>
                <div>
                    <label class="text-xs text-slate-500">Valor de cada parcela</label>
                    <input
                        v-model="form.agreement_installment_amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                    >
                </div>
                <div>
                    <label class="text-xs text-slate-500">Entrada (opcional)</label>
                    <input
                        v-model="form.agreement_down_payment"
                        type="number"
                        step="0.01"
                        min="0"
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                    >
                </div>
                <div class="lg:col-span-2">
                    <label class="text-xs text-slate-500">Observações do acordo</label>
                    <input
                        v-model="form.agreement_notes"
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                        placeholder="Condições, multa, etc."
                    >
                </div>
                <div
                    v-if="!editingId"
                    class="flex items-center gap-2 lg:col-span-2"
                >
                    <input
                        id="sync_recurrence"
                        v-model="form.sync_recurrence"
                        type="checkbox"
                        class="rounded border-slate-600 bg-slate-950 text-emerald-500"
                    >
                    <label
                        for="sync_recurrence"
                        class="text-sm text-slate-300"
                    >Criar recorrência mensal na previsão (parcelas fixas até o fim do acordo)</label>
                </div>
                <p
                    v-if="formError"
                    class="text-sm text-rose-400 lg:col-span-2"
                >
                    {{ formError }}
                </p>
            </form>
            <template #footer>
                <div class="dz-modal-footer">
                    <button
                        type="button"
                        class="dz-btn dz-btn-ghost"
                        @click="fecharFormulario"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        form="form-divida"
                        class="dz-btn dz-btn-primary disabled:opacity-50"
                        :disabled="saving"
                    >
                        {{ saving ? 'Salvando…' : (editingId ? 'Atualizar' : 'Cadastrar') }}
                    </button>
                </div>
            </template>
        </Modal>

        <Modal
            v-model:show="bulkModalOpen"
            title="Adicionar dívidas em massa"
        >
            <div class="space-y-3">
                <p class="text-xs text-slate-500">
                    Preencha várias linhas rapidamente e salve tudo de uma vez.
                </p>
                <div class="space-y-2">
                    <div
                        v-for="(row, idx) in bulkRows"
                        :key="idx"
                        class="grid gap-2 rounded-lg border border-slate-800 bg-slate-950/60 p-3 md:grid-cols-6"
                    >
                        <input
                            v-model="row.title"
                            class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100 md:col-span-2"
                            placeholder="Título *"
                        >
                        <input
                            v-model="row.principal_amount"
                            type="number"
                            min="0.01"
                            step="0.01"
                            class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100"
                            placeholder="Principal *"
                        >
                        <input
                            v-model="row.balance_amount"
                            type="number"
                            min="0"
                            step="0.01"
                            class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100"
                            placeholder="Saldo"
                        >
                        <input
                            v-model="row.creditor"
                            class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100"
                            placeholder="Credor"
                        >
                        <button
                            type="button"
                            class="rounded-lg border border-slate-700 px-2 py-2 text-xs text-slate-300 hover:bg-slate-800"
                            @click="removeBulkRow(idx)"
                        >
                            Remover
                        </button>
                    </div>
                </div>
                <button
                    type="button"
                    class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-300 hover:bg-slate-800"
                    @click="addBulkRow"
                >
                    + Adicionar linha
                </button>
                <p
                    v-if="formError"
                    class="text-sm text-rose-400"
                >
                    {{ formError }}
                </p>
            </div>
            <template #footer>
                <div class="dz-modal-footer">
                    <button
                        type="button"
                        class="dz-btn dz-btn-ghost"
                        @click="bulkModalOpen = false"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="dz-btn dz-btn-primary disabled:opacity-50"
                        :disabled="saving"
                        @click="salvarEmMassa"
                    >
                        {{ saving ? 'Salvando…' : 'Salvar em massa' }}
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Confirmação de exclusão -->
        <Modal
            v-model:show="deleteModalOpen"
            title="Excluir dívida?"
        >
            <p class="text-sm text-slate-400">
                Esta ação não pode ser desfeita. Se existir recorrência vinculada à previsão de caixa, ela também será removida.
            </p>
            <p
                v-if="deleteTargetTitle"
                class="mt-3 rounded-lg border border-slate-800 bg-slate-950/80 px-3 py-2 text-sm font-medium text-white"
            >
                {{ deleteTargetTitle }}
            </p>
            <template #footer>
                <div class="dz-modal-footer">
                    <button
                        type="button"
                        class="dz-btn dz-btn-ghost"
                        :disabled="deleting"
                        @click="deleteModalOpen = false"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="dz-btn border border-rose-700 bg-rose-600 text-white hover:bg-rose-500 disabled:opacity-50"
                        :disabled="deleting"
                        @click="confirmarExclusao"
                    >
                        {{ deleting ? 'Excluindo…' : 'Excluir' }}
                    </button>
                </div>
            </template>
        </Modal>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import BulkSelectionBar from '../components/BulkSelectionBar.vue';
import DzDatePicker from '../components/DzDatePicker.vue';
import Modal from '../components/Modal.vue';
import { useDebts } from '../composables/useDebts';
import { useToastStore } from '../stores/toast';
import { FORM_DATE_MAX, FORM_DATE_MIN } from '../utils/dateRange';

const toast = useToastStore();
const {
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
} = useDebts(toast);

function formatBrl(v) {
    if (v === null || v === undefined || v === '') {
        return '—';
    }
    return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function labelStatus(s) {
    return debtStatuses.find((x) => x.value === s)?.label ?? s;
}

onMounted(async () => {
    await init();
});
</script>
