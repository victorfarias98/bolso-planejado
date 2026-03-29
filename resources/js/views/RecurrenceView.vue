<template>
    <div>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-white">
                    Recorrências mensais
                </h1>
                <p class="mt-1 text-slate-400">
                    Salário, aluguel e outras entradas/saídas fixas no mesmo dia do mês.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="dz-btn dz-btn-primary px-4 text-sm font-medium" @click="abrirNova">Nova recorrência</button>
                <button type="button" class="dz-btn dz-btn-ghost px-4 text-sm font-medium" @click="abrirAdicaoEmMassa">Adição em massa</button>
            </div>
        </div>
        <button
            type="button"
            class="dz-fab md:hidden"
            aria-label="Nova recorrência"
            @click="abrirNova"
        >
            +
        </button>
        <BulkSelectionBar
            v-if="selectedCount"
            :selected-count="selectedCount"
            singular-label="recorrência selecionada"
            plural-label="recorrências selecionadas"
            action-label="Excluir selecionadas"
            @action="abrirExclusaoSelecionadas"
        />

        <div class="mt-8 space-y-3 md:hidden">
            <template v-if="loading">
                <div class="dz-skeleton h-24 w-full rounded-xl" />
                <div class="dz-skeleton h-24 w-full rounded-xl" />
            </template>
            <template v-else>
                <div
                    v-for="r in displayedSeries"
                    :key="r.id"
                    class="dz-mobile-card rounded-xl border border-slate-800 bg-slate-900/40 p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-white">{{ r.description || '—' }}</p>
                            <p class="mt-1 text-xs text-slate-500">Cadastro: {{ formatIsoDate(r.created_at) }}</p>
                        </div>
                        <input
                            type="checkbox"
                            :checked="selectedIds.includes(r.id)"
                            class="rounded border-slate-600 bg-slate-950 text-emerald-500"
                            @change="toggleSelection(r.id)"
                        >
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <p class="text-slate-400">Dia do mês</p>
                        <p class="text-right text-slate-300">{{ r.day_of_month }}</p>
                        <p class="text-slate-400">Valor</p>
                        <p class="text-right text-slate-200">{{ Number(r.amount).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}</p>
                        <p class="text-slate-400">Tipo</p>
                        <p class="text-right" :class="r.type === 'income' ? 'text-emerald-300' : 'text-rose-300'">{{ r.type === 'income' ? 'Entrada' : 'Saída' }}</p>
                        <p class="text-slate-400">Início</p>
                        <p class="text-right text-slate-300">{{ r.start_on }}</p>
                    </div>
                    <div class="mt-3 flex items-center gap-2">
                        <button type="button" class="dz-btn dz-btn-ghost flex-1 text-xs" @click="toggleActive(r)">
                            {{ r.is_active ? 'Ativa: Sim' : 'Ativa: Não' }}
                        </button>
                    </div>
                    <div class="mt-2 flex gap-2">
                        <button type="button" class="dz-btn dz-btn-ghost flex-1 text-xs" @click="iniciarEdicao(r)">Editar</button>
                        <button type="button" class="dz-btn flex-1 border border-rose-800/60 text-xs text-rose-300 hover:bg-rose-950/30" @click="remover(r.id)">Excluir</button>
                    </div>
                </div>
                <div v-if="!displayedSeries.length" class="rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-8 text-center text-slate-500">
                    Nenhuma recorrência.
                </div>
            </template>
        </div>

        <div class="mt-8 hidden overflow-x-auto rounded-xl border border-slate-800 md:block">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-800 bg-slate-900/60 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3"><input type="checkbox" :checked="allVisibleSelected" class="rounded border-slate-600 bg-slate-950 text-emerald-500" @change="toggleSelectAllVisible"></th>
                        <SortableTh
                            column="created_at"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Cadastro
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
                            column="day_of_month"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Dia
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
                            column="type"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Tipo
                        </SortableTh>
                        <SortableTh
                            column="start_on"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Início
                        </SortableTh>
                        <SortableTh
                            column="is_active"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Ativa
                        </SortableTh>
                        <th class="px-4 py-3" />
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="r in displayedSeries"
                        :key="r.id"
                        class="border-b border-slate-800/80"
                    >
                        <td class="px-4 py-3"><input type="checkbox" :checked="selectedIds.includes(r.id)" class="rounded border-slate-600 bg-slate-950 text-emerald-500" @change="toggleSelection(r.id)"></td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-500">
                            {{ formatIsoDate(r.created_at) }}
                        </td>
                        <td class="px-4 py-3 text-white">
                            {{ r.description || '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-300">
                            {{ r.day_of_month }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-200">
                            {{ Number(r.amount).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}
                        </td>
                        <td class="px-4 py-3">
                            <span :class="r.type === 'income' ? 'text-emerald-400' : 'text-rose-400'">
                                {{ r.type === 'income' ? 'Entrada' : 'Saída' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-400">
                            {{ r.start_on }}
                        </td>
                        <td class="px-4 py-3">
                            <button
                                type="button"
                                class="text-xs"
                                :class="r.is_active ? 'text-emerald-400' : 'text-slate-500'"
                                @click="toggleActive(r)"
                            >
                                {{ r.is_active ? 'Sim' : 'Não' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" class="mr-3 text-xs text-emerald-400 hover:underline" @click="iniciarEdicao(r)">Editar</button>
                            <button type="button" class="text-xs text-rose-400 hover:underline" @click="remover(r.id)">Excluir</button>
                        </td>
                    </tr>
                    <tr v-if="!displayedSeries.length && !loading">
                        <td
                            colspan="8"
                            class="px-4 py-8 text-center text-slate-500"
                        >
                            Nenhuma recorrência.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal v-model:show="formModalOpen" title="Nova recorrência">
            <form id="form-recorrencia" class="grid gap-3 md:grid-cols-2 lg:grid-cols-4" @submit.prevent="criar">
                <div><label class="text-xs text-slate-500">Conta</label><select v-model="form.financial_account_id" required class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"><option disabled value="">Selecione</option><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.name }}</option></select></div>
                <div>
                    <label class="text-xs text-slate-500">Categoria</label>
                    <div class="mt-1 flex gap-2">
                        <select v-model="form.category_id" class="min-w-0 flex-1 rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"><option value="">—</option><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option></select>
                        <button type="button" class="rounded-lg border border-slate-700 px-2 py-2 text-xs text-slate-300 hover:bg-slate-800 whitespace-nowrap" @click="abrirNovaCategoria(form)">Nova</button>
                    </div>
                </div>
                <div><label class="text-xs text-slate-500">Tipo</label><select v-model="form.type" required class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"><option value="income">Entrada</option><option value="expense">Saída</option></select></div>
                <div><label class="text-xs text-slate-500">Valor</label><input v-model="form.amount" type="number" step="0.01" min="0.01" required class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"></div>
                <div><label class="text-xs text-slate-500">Dia do mês</label><input v-model.number="form.day_of_month" type="number" min="1" max="31" required class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"></div>
                <div><label class="text-xs text-slate-500">Início</label><DzDatePicker v-model="form.start_on" class="mt-1 w-full" input-class="w-full" :min-date="FORM_DATE_MIN" :max-date="FORM_DATE_MAX" required /></div>
                <div><label class="text-xs text-slate-500">Fim</label><DzDatePicker v-model="form.end_on" class="mt-1 w-full" input-class="w-full" :min-date="FORM_DATE_MIN" :max-date="FORM_DATE_MAX" /></div>
                <div><label class="text-xs text-slate-500">Máx. ocorrências</label><input v-model.number="form.max_occurrences" type="number" min="1" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"></div>
                <div class="md:col-span-2 lg:col-span-4"><label class="text-xs text-slate-500">Descrição</label><input v-model="form.description" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"></div>
                <p v-if="formError" class="text-sm text-rose-400 md:col-span-2 lg:col-span-4">{{ formError }}</p>
            </form>
            <template #footer><div class="dz-modal-footer"><button type="button" class="dz-btn dz-btn-ghost" @click="formModalOpen = false">Cancelar</button><button type="submit" form="form-recorrencia" class="dz-btn dz-btn-primary disabled:opacity-50" :disabled="saving">{{ saving ? 'Salvando…' : 'Salvar' }}</button></div></template>
        </Modal>

        <Modal v-model:show="editModalOpen" title="Editar recorrência">
            <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                <div><label class="text-xs text-slate-500">Conta</label><select v-model="editDraft.financial_account_id" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.name }}</option></select></div>
                <div>
                    <label class="text-xs text-slate-500">Categoria</label>
                    <div class="mt-1 flex gap-2">
                        <select v-model="editDraft.category_id" class="min-w-0 flex-1 rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"><option value="">—</option><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option></select>
                        <button type="button" class="rounded-lg border border-slate-700 px-2 py-2 text-xs text-slate-300 hover:bg-slate-800 whitespace-nowrap" @click="abrirNovaCategoria(editDraft)">Nova</button>
                    </div>
                </div>
                <div><label class="text-xs text-slate-500">Tipo</label><select v-model="editDraft.type" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"><option value="income">Entrada</option><option value="expense">Saída</option></select></div>
                <div><label class="text-xs text-slate-500">Valor</label><input v-model="editDraft.amount" type="number" step="0.01" min="0.01" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"></div>
                <div><label class="text-xs text-slate-500">Dia do mês</label><input v-model.number="editDraft.day_of_month" type="number" min="1" max="31" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"></div>
                <div><label class="text-xs text-slate-500">Início</label><DzDatePicker v-model="editDraft.start_on" class="mt-1 w-full" input-class="w-full" :min-date="FORM_DATE_MIN" :max-date="FORM_DATE_MAX" /></div>
                <div><label class="text-xs text-slate-500">Fim</label><DzDatePicker v-model="editDraft.end_on" class="mt-1 w-full" input-class="w-full" :min-date="FORM_DATE_MIN" :max-date="FORM_DATE_MAX" /></div>
                <div><label class="text-xs text-slate-500">Máx. ocorrências</label><input v-model.number="editDraft.max_occurrences" type="number" min="1" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"></div>
                <div class="md:col-span-2 lg:col-span-4"><label class="text-xs text-slate-500">Descrição</label><input v-model="editDraft.description" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"></div>
                <p v-if="editError" class="text-sm text-rose-400 md:col-span-2 lg:col-span-4">{{ editError }}</p>
            </div>
            <template #footer><div class="dz-modal-footer"><button type="button" class="dz-btn dz-btn-ghost" @click="cancelarEdicao">Cancelar</button><button type="button" class="dz-btn dz-btn-primary" @click="salvarEdicao">Salvar alterações</button></div></template>
        </Modal>

        <Modal v-model:show="deleteModalOpen" title="Excluir recorrências?">
            <p v-if="deleteHasLinkedDebt" class="text-sm text-amber-300">Atenção: esta recorrência está vinculada a um acordo de dívida. Excluir pode quebrar o acordo e reduzir a previsibilidade. Recomendamos não excluir, a menos que você vá ajustar a dívida manualmente depois.</p>
            <p v-else class="text-sm text-slate-400">Esta ação não pode ser desfeita.</p>
            <p class="mt-3 rounded-lg border border-slate-800 bg-slate-950/80 px-3 py-2 text-sm font-medium text-white">{{ deleteTargetLabel }}</p>
            <template #footer><div class="dz-modal-footer"><button type="button" class="dz-btn dz-btn-ghost" :disabled="deleting" @click="deleteModalOpen = false">Cancelar</button><button type="button" class="dz-btn border border-rose-700 bg-rose-600 text-white hover:bg-rose-500 disabled:opacity-50" :disabled="deleting" @click="confirmarExclusao">{{ deleting ? 'Excluindo…' : 'Excluir' }}</button></div></template>
        </Modal>

        <Modal v-model:show="newCategoryModalOpen" title="Nova categoria">
            <div>
                <label class="text-xs text-slate-500">Nome</label>
                <input
                    v-model="newCategoryName"
                    type="text"
                    maxlength="255"
                    class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
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

        <Modal v-model:show="bulkModalOpen" title="Adicionar recorrências em massa">
            <div class="space-y-2">
                <div v-for="(row, idx) in bulkRows" :key="idx" class="grid gap-2 rounded-lg border border-slate-800 bg-slate-950/60 p-3 md:grid-cols-6">
                    <select v-model="row.financial_account_id" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100"><option value="">Conta *</option><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.name }}</option></select>
                    <input v-model="row.amount" type="number" min="0.01" step="0.01" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100" placeholder="Valor *">
                    <input v-model.number="row.day_of_month" type="number" min="1" max="31" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100" placeholder="Dia">
                    <DzDatePicker v-model="row.start_on" class="min-w-0 w-full" input-class="w-full" :min-date="FORM_DATE_MIN" :max-date="FORM_DATE_MAX" />
                    <input v-model="row.description" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100" placeholder="Descrição">
                    <button type="button" class="rounded-lg border border-slate-700 px-2 py-2 text-xs text-slate-300 hover:bg-slate-800" @click="removeBulkRow(idx)">Remover</button>
                </div>
            </div>
            <button type="button" class="mt-2 rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-300 hover:bg-slate-800" @click="addBulkRow">+ Adicionar linha</button>
            <p v-if="formError" class="mt-2 text-sm text-rose-400">{{ formError }}</p>
            <template #footer><div class="dz-modal-footer"><button type="button" class="dz-btn dz-btn-ghost" @click="bulkModalOpen = false">Cancelar</button><button type="button" class="dz-btn dz-btn-primary disabled:opacity-50" :disabled="saving" @click="salvarEmMassa">{{ saving ? 'Salvando…' : 'Salvar em massa' }}</button></div></template>
        </Modal>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import BulkSelectionBar from '../components/BulkSelectionBar.vue';
import DzDatePicker from '../components/DzDatePicker.vue';
import Modal from '../components/Modal.vue';
import { useToastStore } from '../stores/toast';
import SortableTh from '../components/SortableTh.vue';
import { useRecurrence } from '../composables/useRecurrence';
import { FORM_DATE_MAX, FORM_DATE_MIN } from '../utils/dateRange';

const toast = useToastStore();
const {
    accounts,
    categories,
    displayedSeries,
    sortKey,
    sortDir,
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
} = useRecurrence(toast);

function formatIsoDate(iso) {
    if (! iso) {
        return '—';
    }
    return new Date(iso).toLocaleDateString('pt-BR');
}

onMounted(carregar);
</script>
