<template>
    <div>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-white">
                    Transações
                </h1>
                <p class="mt-1 text-slate-400">
                    Lançamentos realizados ou agendados — alimentam a previsão diária.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    class="dz-btn dz-btn-ghost px-4 text-sm font-medium disabled:opacity-50"
                    :disabled="exportingPdf"
                    @click="baixarRelatorioPdf"
                >
                    {{ exportingPdf ? 'Gerando PDF…' : 'Relatório PDF do mês' }}
                </button>
                <button
                    type="button"
                    class="dz-btn dz-btn-primary px-4 text-sm font-medium"
                    @click="abrirNova"
                >
                    Nova transação
                </button>
                <button
                    type="button"
                    class="dz-btn dz-btn-ghost px-4 text-sm font-medium"
                    @click="abrirAdicaoEmMassa"
                >
                    Adição em massa
                </button>
            </div>
        </div>
        <button
            type="button"
            class="dz-fab md:hidden"
            aria-label="Nova transação"
            @click="abrirNova"
        >
            +
        </button>

        <div class="mt-6 flex flex-wrap gap-3">
            <div class="flex items-center gap-2 rounded-lg border border-slate-800 bg-slate-900/40 px-2 py-1.5">
                <button
                    type="button"
                    class="rounded-md border border-slate-700 px-2 py-1 text-xs text-slate-300 transition hover:bg-slate-800"
                    @click="prevMonth"
                >
                    Mês anterior
                </button>
                <input
                    :value="month"
                    type="month"
                    class="dz-input min-h-0 rounded-md px-2 py-1 text-sm"
                    @change="setMonth($event.target.value)"
                >
                <button
                    type="button"
                    class="rounded-md border border-slate-700 px-2 py-1 text-xs text-slate-300 transition hover:bg-slate-800"
                    @click="nextMonth"
                >
                    Próximo mês
                </button>
            </div>
            <select
                v-model="filters.financial_account_id"
                class="dz-select max-w-xs"
                @change="carregar"
            >
                <option value="">
                    Todas as contas
                </option>
                <option
                    v-for="a in accounts"
                    :key="a.id"
                    :value="a.id"
                >
                    {{ a.name }}
                </option>
            </select>
            <select
                v-model="filters.status"
                class="dz-select max-w-xs"
                @change="carregar"
            >
                <option value="">
                    Todos os status
                </option>
                <option value="completed">
                    Realizado
                </option>
                <option value="scheduled">
                    Agendado
                </option>
            </select>
        </div>

        <p class="mt-3 text-xs text-slate-500">
            Período exibido: <span class="font-medium text-slate-300">{{ monthLabel }}</span> ·
            Ordenação: {{ sortHint }}
        </p>
        <BulkSelectionBar
            v-if="selectedCount"
            :selected-count="selectedCount"
            singular-label="transação selecionada"
            plural-label="transações selecionadas"
            action-label="Excluir selecionadas"
            @action="abrirExclusaoSelecionadas"
        />

        <div class="mt-6 space-y-3 md:hidden">
            <template v-if="loading">
                <div class="dz-skeleton h-24 w-full rounded-xl" />
                <div class="dz-skeleton h-24 w-full rounded-xl" />
                <div class="dz-skeleton h-24 w-full rounded-xl" />
            </template>
            <template v-else>
                <template v-for="month in organizedMonths" :key="`m-${month.key}`">
                    <div class="dz-mobile-card rounded-xl border border-slate-800 bg-slate-900/50 px-3 py-2">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-300">{{ month.label }}</p>
                            <p class="text-[11px] text-slate-400">Saldo: {{ formatCurrency(month.balance) }}</p>
                        </div>
                    </div>
                    <template v-for="day in month.days" :key="`d-${month.key}-${day.key}`">
                        <p class="px-1 text-[11px] text-slate-500">{{ day.label }}</p>
                        <div
                            v-for="t in day.items"
                            :key="`t-${t.id}`"
                            class="dz-mobile-card rounded-xl border border-slate-800 bg-slate-900/35 p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-white">{{ t.description || '—' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ t.financial_account?.name ?? '—' }}</p>
                                </div>
                                <input
                                    type="checkbox"
                                    :checked="selectedIds.includes(t.id)"
                                    class="rounded border-slate-600 bg-slate-950 text-emerald-500"
                                    @change="toggleSelection(t.id)"
                                >
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                <p class="text-slate-400">Data</p>
                                <p class="text-right text-slate-300">{{ formatDate(t.occurred_on) }}</p>
                                <p class="text-slate-400">Tipo</p>
                                <p class="text-right" :class="t.type === 'income' ? 'text-emerald-300' : 'text-rose-300'">{{ t.type === 'income' ? 'Entrada' : 'Saída' }}</p>
                                <p class="text-slate-400">Valor</p>
                                <p class="text-right text-slate-200">{{ Number(t.amount).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}</p>
                                <p class="text-slate-400">Status</p>
                                <p class="text-right text-slate-300">{{ t.status === 'completed' ? 'Realizado' : 'Agendado' }}</p>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <button type="button" class="dz-btn dz-btn-ghost flex-1 text-xs" @click="iniciarEdicao(t)">Editar</button>
                                <button type="button" class="dz-btn flex-1 border border-rose-800/60 text-xs text-rose-300 hover:bg-rose-950/30" @click="remover(t.id)">Excluir</button>
                            </div>
                        </div>
                    </template>
                </template>
                <div v-if="!items.length" class="rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-8 text-center text-slate-500">
                    Nenhuma transação.
                </div>
            </template>
        </div>

        <div class="mt-6 hidden overflow-x-auto rounded-xl border border-slate-800 transition-colors duration-300 hover:border-slate-700 md:block">
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
                        <SortableTh
                            column="created_at"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Inclusão
                        </SortableTh>
                        <SortableTh
                            column="occurred_on"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Data
                        </SortableTh>
                        <SortableTh
                            column="description"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Descrição
                        </SortableTh>
                        <SortableTh
                            column="account_name"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Conta
                        </SortableTh>
                        <SortableTh
                            column="type"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Tipo
                        </SortableTh>
                        <SortableTh
                            column="amount"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Valor
                        </SortableTh>
                        <SortableTh
                            column="status"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Status
                        </SortableTh>
                        <th class="whitespace-nowrap px-4 py-3 text-right">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="loading">
                        <tr>
                            <td
                                colspan="9"
                                class="dz-loading-pulse px-4 py-10 text-center text-slate-500"
                            >
                                Carregando…
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <template
                            v-for="month in organizedMonths"
                            :key="month.key"
                        >
                            <tr class="border-b border-slate-800/80 bg-slate-900/70">
                                <td colspan="9" class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-300">
                                            {{ month.label }}
                                        </p>
                                        <div class="flex flex-wrap gap-2 text-[11px]">
                                            <span class="rounded-md border border-slate-700 bg-slate-950/60 px-2 py-1 text-slate-300">
                                                Entradas: {{ formatCurrency(month.income) }}
                                            </span>
                                            <span class="rounded-md border border-slate-700 bg-slate-950/60 px-2 py-1 text-slate-300">
                                                Saídas: {{ formatCurrency(month.expense) }}
                                            </span>
                                            <span
                                                class="rounded-md border px-2 py-1"
                                                :class="month.balance >= 0 ? 'border-emerald-800/60 bg-emerald-950/30 text-emerald-300' : 'border-rose-800/60 bg-rose-950/30 text-rose-300'"
                                            >
                                                Saldo: {{ formatCurrency(month.balance) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <template
                                v-for="day in month.days"
                                :key="`${month.key}-${day.key}`"
                            >
                                <tr class="border-b border-slate-800/60 bg-slate-900/30">
                                    <td colspan="9" class="px-4 py-2 text-[11px] text-slate-400">
                                        {{ day.label }}
                                    </td>
                                </tr>
                                <tr
                                    v-for="t in day.items"
                                    :key="t.id"
                                    class="border-b border-slate-800/80 transition-colors duration-200 hover:bg-slate-800/25"
                                >
                                <td class="px-4 py-3">
                                    <input
                                        type="checkbox"
                                        :checked="selectedIds.includes(t.id)"
                                        class="rounded border-slate-600 bg-slate-950 text-emerald-500"
                                        @change="toggleSelection(t.id)"
                                    >
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-400">
                                    {{ formatIsoDate(t.created_at) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-300">
                                    {{ formatDate(t.occurred_on) }}
                                </td>
                                <td class="max-w-[180px] truncate px-4 py-3 text-white">
                                    {{ t.description || '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-400">
                                    {{ t.financial_account?.name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="t.type === 'income' ? 'text-emerald-400' : 'text-rose-400'">
                                        {{ t.type === 'income' ? 'Entrada' : 'Saída' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-200">
                                    {{ Number(t.amount).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}
                                </td>
                                <td class="px-4 py-3 text-slate-400">
                                    {{ t.status === 'completed' ? 'Realizado' : 'Agendado' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <button
                                        type="button"
                                        class="mr-3 text-xs font-medium text-emerald-400 transition hover:text-emerald-300"
                                        @click="iniciarEdicao(t)"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        type="button"
                                        class="text-xs text-rose-400/90 transition hover:text-rose-300"
                                        @click="remover(t.id)"
                                    >
                                        Excluir
                                    </button>
                                </td>
                                </tr>
                            </template>
                        </template>
                        <tr v-if="!items.length">
                            <td
                                colspan="9"
                                class="px-4 py-8 text-center text-slate-500"
                            >
                                Nenhuma transação.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div
            v-if="meta.last_page > 1"
            class="mt-4 flex justify-center gap-2 text-sm"
        >
            <button
                type="button"
                class="rounded-lg border border-slate-700 px-3 py-1.5 transition hover:bg-slate-800 disabled:opacity-40"
                :disabled="meta.current_page <= 1"
                @click="page(meta.current_page - 1)"
            >
                Anterior
            </button>
            <span class="px-2 py-1 text-slate-500">{{ meta.current_page }} / {{ meta.last_page }}</span>
            <button
                type="button"
                class="rounded-lg border border-slate-700 px-3 py-1.5 transition hover:bg-slate-800 disabled:opacity-40"
                :disabled="meta.current_page >= meta.last_page"
                @click="page(meta.current_page + 1)"
            >
                Próxima
            </button>
        </div>

        <Modal
            v-model:show="formModalOpen"
            title="Nova transação"
        >
            <form
                id="form-transacao"
                class="grid gap-3 md:grid-cols-2 lg:grid-cols-3"
                @submit.prevent="criar"
            >
                <div><label class="text-xs text-slate-500">Conta</label><select v-model="form.financial_account_id" required class="dz-select mt-1 w-full"><option disabled value="">Selecione</option><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.name }}</option></select></div>
                <div>
                    <label class="text-xs text-slate-500">Categoria</label>
                    <div class="mt-1 flex gap-2">
                        <select v-model="form.category_id" class="dz-select min-w-0 flex-1"><option value="">—</option><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option></select>
                        <button type="button" class="dz-btn dz-btn-ghost shrink-0 px-2 text-xs whitespace-nowrap" @click="abrirNovaCategoria(form)">Nova</button>
                    </div>
                </div>
                <div><label class="text-xs text-slate-500">Tipo</label><select v-model="form.type" required class="dz-select mt-1 w-full"><option value="income">Entrada</option><option value="expense">Saída</option></select></div>
                <div><label class="text-xs text-slate-500">Valor</label><input v-model="form.amount" type="number" step="0.01" min="0.01" required class="dz-input mt-1 w-full"></div>
                <div><label class="text-xs text-slate-500">Data</label><DzDatePicker v-model="form.occurred_on" class="mt-1 w-full" input-class="w-full" :min-date="FORM_DATE_MIN" :max-date="FORM_DATE_MAX" required /></div>
                <div><label class="text-xs text-slate-500">Status</label><select v-model="form.status" required class="dz-select mt-1 w-full"><option value="completed">Realizado</option><option value="scheduled">Agendado</option></select></div>
                <div class="md:col-span-2 lg:col-span-3"><label class="text-xs text-slate-500">Descrição</label><input v-model="form.description" class="dz-input mt-1 w-full"></div>
                <p v-if="formError" class="text-sm text-rose-400 md:col-span-2 lg:col-span-3">{{ formError }}</p>
            </form>
            <template #footer>
                <div class="dz-modal-footer">
                    <button type="button" class="dz-btn dz-btn-ghost" @click="formModalOpen = false">Cancelar</button>
                    <button type="submit" form="form-transacao" class="dz-btn dz-btn-primary disabled:opacity-50" :disabled="saving">{{ saving ? 'Salvando…' : 'Salvar' }}</button>
                </div>
            </template>
        </Modal>

        <Modal
            v-model:show="editModalOpen"
            title="Editar transação"
        >
            <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                <div><label class="text-xs text-slate-500">Conta</label><select v-model="editDraft.financial_account_id" class="dz-select mt-1 w-full"><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.name }}</option></select></div>
                <div>
                    <label class="text-xs text-slate-500">Categoria</label>
                    <div class="mt-1 flex gap-2">
                        <select v-model="editDraft.category_id" class="dz-select min-w-0 flex-1"><option value="">—</option><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option></select>
                        <button type="button" class="dz-btn dz-btn-ghost shrink-0 px-2 text-xs whitespace-nowrap" @click="abrirNovaCategoria(editDraft)">Nova</button>
                    </div>
                </div>
                <div><label class="text-xs text-slate-500">Tipo</label><select v-model="editDraft.type" class="dz-select mt-1 w-full"><option value="income">Entrada</option><option value="expense">Saída</option></select></div>
                <div><label class="text-xs text-slate-500">Valor</label><input v-model="editDraft.amount" type="number" step="0.01" min="0.01" class="dz-input mt-1 w-full"></div>
                <div><label class="text-xs text-slate-500">Data</label><DzDatePicker v-model="editDraft.occurred_on" class="mt-1 w-full" input-class="w-full" :min-date="FORM_DATE_MIN" :max-date="FORM_DATE_MAX" /></div>
                <div><label class="text-xs text-slate-500">Status</label><select v-model="editDraft.status" class="dz-select mt-1 w-full"><option value="completed">Realizado</option><option value="scheduled">Agendado</option></select></div>
                <div class="md:col-span-2 lg:col-span-3"><label class="text-xs text-slate-500">Descrição</label><input v-model="editDraft.description" class="dz-input mt-1 w-full"></div>
                <p v-if="editError" class="text-sm text-rose-400 md:col-span-2 lg:col-span-3">{{ editError }}</p>
            </div>
            <template #footer><div class="dz-modal-footer"><button type="button" class="dz-btn dz-btn-ghost" @click="cancelarEdicao">Cancelar</button><button type="button" class="dz-btn dz-btn-primary disabled:opacity-50" :disabled="savingEdit" @click="salvarEdicao">{{ savingEdit ? 'Salvando…' : 'Salvar alterações' }}</button></div></template>
        </Modal>

        <Modal v-model:show="deleteModalOpen" title="Excluir transações?">
            <p class="text-sm text-slate-400">Esta ação não pode ser desfeita.</p>
            <p class="mt-3 rounded-lg border border-slate-800 bg-slate-950/80 px-3 py-2 text-sm font-medium text-white">{{ deleteTargetLabel }}</p>
            <template #footer><div class="dz-modal-footer"><button type="button" class="dz-btn dz-btn-ghost" :disabled="deleting" @click="deleteModalOpen = false">Cancelar</button><button type="button" class="dz-btn border border-rose-700 bg-rose-600 text-white hover:bg-rose-500 disabled:opacity-50" :disabled="deleting" @click="confirmarExclusao">{{ deleting ? 'Excluindo…' : 'Excluir' }}</button></div></template>
        </Modal>

        <Modal v-model:show="bulkModalOpen" title="Adicionar transações em massa">
            <div class="space-y-2">
                <div v-for="(row, idx) in bulkRows" :key="idx" class="grid gap-2 rounded-lg border border-slate-800 bg-slate-950/60 p-3 md:grid-cols-7">
                    <select v-model="row.financial_account_id" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100"><option value="">Conta *</option><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.name }}</option></select>
                    <input v-model="row.amount" type="number" min="0.01" step="0.01" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100" placeholder="Valor *">
                    <DzDatePicker v-model="row.occurred_on" class="w-full min-w-0" input-class="w-full" :min-date="FORM_DATE_MIN" :max-date="FORM_DATE_MAX" />
                    <select v-model="row.type" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100"><option value="income">Entrada</option><option value="expense">Saída</option></select>
                    <select v-model="row.status" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100"><option value="completed">Realizado</option><option value="scheduled">Agendado</option></select>
                    <input v-model="row.description" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100" placeholder="Descrição">
                    <button type="button" class="rounded-lg border border-slate-700 px-2 py-2 text-xs text-slate-300 hover:bg-slate-800" @click="removeBulkRow(idx)">Remover</button>
                </div>
            </div>
            <button type="button" class="mt-2 rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-300 hover:bg-slate-800" @click="addBulkRow">+ Adicionar linha</button>
            <p v-if="formError" class="mt-2 text-sm text-rose-400">{{ formError }}</p>
            <template #footer><div class="dz-modal-footer"><button type="button" class="dz-btn dz-btn-ghost" @click="bulkModalOpen = false">Cancelar</button><button type="button" class="dz-btn dz-btn-primary disabled:opacity-50" :disabled="saving" @click="salvarEmMassa">{{ saving ? 'Salvando…' : 'Salvar em massa' }}</button></div></template>
        </Modal>

        <Modal v-model:show="newCategoryModalOpen" title="Nova categoria">
            <div>
                <label class="text-xs text-slate-500">Nome</label>
                <input
                    v-model="newCategoryName"
                    type="text"
                    maxlength="255"
                    class="dz-input mt-1 w-full"
                    placeholder="Ex.: Educação, pets…"
                    @keydown.enter.prevent="salvarNovaCategoria"
                >
            </div>
            <template #footer>
                <div class="dz-modal-footer">
                    <button type="button" class="dz-btn dz-btn-ghost" @click="newCategoryModalOpen = false">Cancelar</button>
                    <button type="button" class="dz-btn dz-btn-primary disabled:opacity-50" :disabled="creatingCategory" @click="salvarNovaCategoria">{{ creatingCategory ? 'Salvando…' : 'Criar' }}</button>
                </div>
            </template>
        </Modal>
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import BulkSelectionBar from '../components/BulkSelectionBar.vue';
import DzDatePicker from '../components/DzDatePicker.vue';
import Modal from '../components/Modal.vue';
import { useToastStore } from '../stores/toast';
import SortableTh from '../components/SortableTh.vue';
import { useTransactions } from '../composables/useTransactions';
import { FORM_DATE_MAX, FORM_DATE_MIN } from '../utils/dateRange';

const toast = useToastStore();
const {
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
} = useTransactions(toast);

function formatDate(d) {
    return d ? new Date(d + 'T12:00:00').toLocaleDateString('pt-BR') : '';
}

function formatIsoDate(iso) {
    if (! iso) {
        return '—';
    }
    return new Date(iso).toLocaleDateString('pt-BR');
}

const monthLabel = computed(() => {
    const [y, m] = String(month.value).split('-').map(Number);
    if (!y || !m) return '';
    return new Date(y, m - 1, 1).toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
});

function formatCurrency(value) {
    return Number(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

const organizedMonths = computed(() => {
    const map = new Map();
    for (const item of items.value) {
        const key = (item.occurred_on ?? '').slice(0, 7) || 'sem-data';
        if (!map.has(key)) {
            map.set(key, {
                key,
                items: [],
                income: 0,
                expense: 0,
            });
        }
        const entry = map.get(key);
        entry.items.push(item);
        const amount = Number(item.amount || 0);
        if (item.type === 'income') {
            entry.income += amount;
        } else {
            entry.expense += amount;
        }
    }

    return Array.from(map.values()).map((month) => {
        const dayMap = new Map();
        for (const item of month.items) {
            const dayKey = item.occurred_on || 'sem-data';
            if (!dayMap.has(dayKey)) {
                dayMap.set(dayKey, []);
            }
            dayMap.get(dayKey).push(item);
        }

        const days = Array.from(dayMap.entries()).map(([dayKey, dayItems]) => ({
            key: dayKey,
            label: dayKey === 'sem-data' ? 'Sem data' : formatDate(dayKey),
            items: dayItems,
        }));

        return {
            key: month.key,
            label: month.key === 'sem-data'
                ? 'Sem data'
                : new Date(`${month.key}-01T12:00:00`).toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' }),
            income: month.income,
            expense: month.expense,
            balance: month.income - month.expense,
            days,
        };
    });
});

onMounted(async () => {
    await init();
});
</script>
