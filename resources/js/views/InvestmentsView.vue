<template>
    <div>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-white">Investimentos e caixinhas</h1>
                <p class="mt-1 text-slate-400">Acompanhe crescimento mensal, aportes e recomendações para sair das dívidas.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="dz-btn dz-btn-primary px-4 text-sm font-medium" @click="abrirNovo">Novo investimento</button>
                <button type="button" class="dz-btn dz-btn-ghost px-4 text-sm font-medium" @click="abrirAdicaoEmMassa">Adição em massa</button>
            </div>
        </div>
        <button
            type="button"
            class="dz-fab md:hidden"
            aria-label="Novo investimento"
            @click="abrirNovo"
        >
            +
        </button>

        <BulkSelectionBar
            v-if="selectedCount"
            :selected-count="selectedCount"
            singular-label="investimento selecionado"
            plural-label="investimentos selecionados"
            action-label="Excluir selecionados"
            @action="abrirExclusaoSelecionadas"
        />

        <div v-if="metaTotals" class="mt-6 grid gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4"><p class="text-xs uppercase text-slate-500">Total investido</p><p class="mt-1 text-xl font-semibold text-emerald-300">{{ formatBrl(metaTotals.current_total) }}</p></div>
            <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4"><p class="text-xs uppercase text-slate-500">Aporte mensal</p><p class="mt-1 text-xl font-semibold text-white">{{ formatBrl(metaTotals.monthly_contribution_total) }}</p></div>
            <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4"><p class="text-xs uppercase text-slate-500">Rendimento estimado/mês</p><p class="mt-1 text-xl font-semibold text-white">{{ formatBrl(metaTotals.monthly_yield_estimate_total) }}</p></div>
            <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4"><p class="text-xs uppercase text-slate-500">Ativos</p><p class="mt-1 text-xl font-semibold text-slate-200">{{ metaTotals.active_count }}</p></div>
        </div>

        <div v-if="metaAnalysis" class="mt-6 rounded-xl border border-slate-800 bg-slate-900/30 p-4">
            <h2 class="text-sm font-medium text-slate-300">Análise automática</h2>
            <p class="mt-2 text-xs text-slate-500">Entradas do mês: {{ formatBrl(metaAnalysis.month_income) }} | Saídas: {{ formatBrl(metaAnalysis.month_expense) }} | Sobra: {{ formatBrl(metaAnalysis.month_surplus) }}</p>
            <div class="mt-3 grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                    <p class="text-xs uppercase text-slate-500">Recomendação dívida</p>
                    <p class="mt-1 text-sm font-medium text-rose-300">{{ metaAnalysis.recommended_debt_payment_pct }}% ({{ formatBrl(metaAnalysis.recommended_debt_payment) }})</p>
                </div>
                <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                    <p class="text-xs uppercase text-slate-500">Recomendação aporte</p>
                    <p class="mt-1 text-sm font-medium text-emerald-300">{{ metaAnalysis.recommended_investment_pct }}% ({{ formatBrl(metaAnalysis.recommended_investment_contribution) }})</p>
                </div>
                <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                    <p class="text-xs uppercase text-slate-500">Prazo para quitar dívida</p>
                    <p class="mt-1 text-sm font-medium text-white">
                        {{ metaAnalysis.estimated_debt_payoff_months === null ? 'Sem previsão com a sobra atual' : `${metaAnalysis.estimated_debt_payoff_months} meses` }}
                    </p>
                </div>
            </div>
            <div class="mt-3 grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                    <p class="text-xs uppercase text-slate-500">Cenário conservador (12m)</p>
                    <p class="mt-1 text-sm font-medium text-slate-100">{{ formatBrl(metaAnalysis.scenario_12m?.conservative) }}</p>
                </div>
                <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                    <p class="text-xs uppercase text-slate-500">Cenário base (12m)</p>
                    <p class="mt-1 text-sm font-medium text-emerald-300">{{ formatBrl(metaAnalysis.scenario_12m?.base) }}</p>
                </div>
                <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                    <p class="text-xs uppercase text-slate-500">Cenário otimista (12m)</p>
                    <p class="mt-1 text-sm font-medium text-slate-100">{{ formatBrl(metaAnalysis.scenario_12m?.optimistic) }}</p>
                </div>
            </div>
            <ul class="mt-3 space-y-2 text-sm text-slate-300">
                <li v-for="(r, idx) in metaAnalysis.recommendations" :key="idx">- {{ r }}</li>
            </ul>
        </div>

        <div class="mt-8 space-y-3 md:hidden">
            <template v-if="loading">
                <div class="dz-skeleton h-28 w-full rounded-xl" />
                <div class="dz-skeleton h-28 w-full rounded-xl" />
            </template>
            <template v-else>
                <div
                    v-for="i in displayedItems"
                    :key="i.id"
                    class="dz-mobile-card rounded-xl border border-slate-800 bg-slate-900/40 p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-white">{{ i.title }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ labelType(i.investment_type) }}</p>
                        </div>
                        <input
                            type="checkbox"
                            :checked="selectedIds.includes(i.id)"
                            class="rounded border-slate-600 bg-slate-950 text-emerald-500"
                            @change="toggleSelection(i.id)"
                        >
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <p class="text-slate-400">Valor atual</p>
                        <p class="text-right text-slate-200">{{ formatBrl(i.current_amount) }}</p>
                        <p class="text-slate-400">Aporte/mês</p>
                        <p class="text-right text-slate-300">{{ formatBrl(i.monthly_contribution) }}</p>
                        <p class="text-slate-400">Rende/mês</p>
                        <p class="text-right text-emerald-300">{{ Number(i.monthly_return_rate).toFixed(2) }}%</p>
                        <p class="text-slate-400">Projeção 12m</p>
                        <p class="text-right text-slate-200">{{ formatBrl(i.projected_12m) }}</p>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button type="button" class="dz-btn dz-btn-ghost flex-1 text-xs" @click="abrirEdicao(i)">Editar</button>
                        <button type="button" class="dz-btn flex-1 border border-rose-800/60 text-xs text-rose-300 hover:bg-rose-950/30" @click="abrirExclusao(i)">Excluir</button>
                    </div>
                </div>
                <div v-if="!displayedItems.length" class="rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-8 text-center text-slate-500">
                    Nenhum investimento cadastrado.
                </div>
            </template>
        </div>

        <div class="mt-8 hidden overflow-x-auto rounded-xl border border-slate-800 md:block">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-800 bg-slate-900/60 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3"><input type="checkbox" :checked="allVisibleSelected" class="rounded border-slate-600 bg-slate-950 text-emerald-500" @change="toggleSelectAllVisible"></th>
                        <SortableTh column="created_at" :model-key="sortKey" :model-dir="sortDir" @sort="onSort">Cadastro</SortableTh>
                        <SortableTh column="title" :model-key="sortKey" :model-dir="sortDir" @sort="onSort">Nome</SortableTh>
                        <SortableTh column="current_amount" :model-key="sortKey" :model-dir="sortDir" @sort="onSort">Valor atual</SortableTh>
                        <SortableTh column="monthly_contribution" :model-key="sortKey" :model-dir="sortDir" @sort="onSort">Aporte/mês</SortableTh>
                        <SortableTh column="monthly_return_rate" :model-key="sortKey" :model-dir="sortDir" @sort="onSort">Rende/mês</SortableTh>
                        <SortableTh column="projected_12m" :model-key="sortKey" :model-dir="sortDir" @sort="onSort">Projeção 12m</SortableTh>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading"><td colspan="8" class="dz-loading-pulse px-4 py-8 text-center text-slate-500">Carregando…</td></tr>
                    <template v-else>
                        <tr v-for="i in displayedItems" :key="i.id" class="border-b border-slate-800/80 hover:bg-slate-800/25">
                            <td class="px-4 py-3"><input type="checkbox" :checked="selectedIds.includes(i.id)" class="rounded border-slate-600 bg-slate-950 text-emerald-500" @change="toggleSelection(i.id)"></td>
                            <td class="px-4 py-3 text-slate-500">{{ formatDate(i.created_at) }}</td>
                            <td class="px-4 py-3 text-white">{{ i.title }} <span class="ml-2 text-xs text-slate-500">({{ labelType(i.investment_type) }})</span></td>
                            <td class="px-4 py-3 text-slate-200">{{ formatBrl(i.current_amount) }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ formatBrl(i.monthly_contribution) }}</td>
                            <td class="px-4 py-3 text-emerald-300">{{ Number(i.monthly_return_rate).toFixed(2) }}%</td>
                            <td class="px-4 py-3 text-slate-200">{{ formatBrl(i.projected_12m) }}</td>
                            <td class="px-4 py-3 text-right"><button type="button" class="mr-3 text-xs text-emerald-400 hover:text-emerald-300" @click="abrirEdicao(i)">Editar</button><button type="button" class="text-xs text-rose-400 hover:text-rose-300" @click="abrirExclusao(i)">Excluir</button></td>
                        </tr>
                        <tr v-if="!displayedItems.length"><td colspan="8" class="px-4 py-8 text-center text-slate-500">Nenhum investimento cadastrado.</td></tr>
                    </template>
                </tbody>
            </table>
        </div>

        <Modal v-model:show="formModalOpen" :title="modalTitle">
            <form id="form-investimento" class="grid gap-3 md:grid-cols-2" @submit.prevent="salvar">
                <div><label class="text-xs text-slate-500">Nome</label><input v-model="form.title" required class="dz-input mt-1 w-full"></div>
                <div><label class="text-xs text-slate-500">Tipo</label><select v-model="form.investment_type" class="dz-select mt-1 w-full"><option v-for="t in investmentTypes" :key="t.value" :value="t.value">{{ t.label }}</option></select></div>
                <div><label class="text-xs text-slate-500">Valor atual</label><input v-model="form.current_amount" type="number" step="0.01" min="0" required class="dz-input mt-1 w-full"></div>
                <div><label class="text-xs text-slate-500">Aporte mensal</label><input v-model="form.monthly_contribution" type="number" step="0.01" min="0" class="dz-input mt-1 w-full"></div>
                <div><label class="text-xs text-slate-500">Rendimento ao mês (%)</label><input v-model="form.monthly_return_rate" type="number" step="0.01" min="0" class="dz-input mt-1 w-full"></div>
                <div><label class="text-xs text-slate-500">Dia do aporte</label><input v-model.number="form.contribution_day" type="number" min="1" max="31" class="dz-input mt-1 w-full"></div>
                <div><label class="text-xs text-slate-500">Meta (opcional)</label><input v-model="form.target_amount" type="number" step="0.01" min="0" class="dz-input mt-1 w-full"></div>
                <div><label class="text-xs text-slate-500">Ativo</label><select v-model="form.is_active" class="dz-select mt-1 w-full"><option :value="true">Sim</option><option :value="false">Não</option></select></div>
                <div class="md:col-span-2"><label class="text-xs text-slate-500">Observações</label><input v-model="form.notes" class="dz-input mt-1 w-full"></div>
                <p v-if="formError" class="text-sm text-rose-400 md:col-span-2">{{ formError }}</p>
            </form>
            <template #footer><div class="flex w-full gap-2 sm:justify-end"><button type="button" class="dz-btn dz-btn-ghost flex-1 sm:flex-none" @click="formModalOpen = false">Cancelar</button><button type="submit" form="form-investimento" class="dz-btn dz-btn-primary flex-1 sm:flex-none disabled:opacity-50" :disabled="saving">{{ saving ? 'Salvando…' : 'Salvar' }}</button></div></template>
        </Modal>

        <Modal v-model:show="bulkModalOpen" title="Adicionar investimentos em massa">
            <div class="space-y-2">
                <div v-for="(row, idx) in bulkRows" :key="idx" class="grid gap-2 rounded-lg border border-slate-800 bg-slate-950/60 p-3 md:grid-cols-6">
                    <input v-model="row.title" class="dz-input md:col-span-2" placeholder="Nome *">
                    <select v-model="row.investment_type" class="dz-select"><option v-for="t in investmentTypes" :key="t.value" :value="t.value">{{ t.label }}</option></select>
                    <input v-model="row.current_amount" type="number" step="0.01" min="0" class="dz-input" placeholder="Valor atual *">
                    <input v-model="row.monthly_contribution" type="number" step="0.01" min="0" class="dz-input" placeholder="Aporte/mês">
                    <input v-model="row.monthly_return_rate" type="number" step="0.01" min="0" class="dz-input" placeholder="Rende %">
                    <button type="button" class="dz-btn dz-btn-ghost text-xs" @click="removeBulkRow(idx)">Remover</button>
                </div>
            </div>
            <button type="button" class="dz-btn dz-btn-ghost mt-2 text-xs" @click="addBulkRow">+ Adicionar linha</button>
            <p v-if="formError" class="mt-2 text-sm text-rose-400">{{ formError }}</p>
            <template #footer><div class="flex w-full gap-2 sm:justify-end"><button type="button" class="dz-btn dz-btn-ghost flex-1 sm:flex-none" @click="bulkModalOpen = false">Cancelar</button><button type="button" class="dz-btn dz-btn-primary flex-1 sm:flex-none disabled:opacity-50" :disabled="saving" @click="salvarEmMassa">{{ saving ? 'Salvando…' : 'Salvar em massa' }}</button></div></template>
        </Modal>

        <Modal v-model:show="deleteModalOpen" title="Excluir investimento(s)?">
            <p class="text-sm text-slate-400">Esta ação não pode ser desfeita.</p>
            <p class="mt-3 rounded-lg border border-slate-800 bg-slate-950/80 px-3 py-2 text-sm font-medium text-white">{{ deleteTargetLabel }}</p>
            <template #footer><div class="flex w-full gap-2 sm:justify-end"><button type="button" class="dz-btn dz-btn-ghost flex-1 sm:flex-none" :disabled="deleting" @click="deleteModalOpen = false">Cancelar</button><button type="button" class="dz-btn flex-1 border border-rose-700 bg-rose-600 text-white hover:bg-rose-500 sm:flex-none disabled:opacity-50" :disabled="deleting" @click="confirmarExclusao">{{ deleting ? 'Excluindo…' : 'Excluir' }}</button></div></template>
        </Modal>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import BulkSelectionBar from '../components/BulkSelectionBar.vue';
import Modal from '../components/Modal.vue';
import SortableTh from '../components/SortableTh.vue';
import { useInvestments } from '../composables/useInvestments';
import { useToastStore } from '../stores/toast';

const toast = useToastStore();
const {
    loading, saving, deleting, formError, sortKey, sortDir, displayedItems, metaTotals, metaAnalysis,
    formModalOpen, bulkModalOpen, deleteModalOpen, selectedIds, selectedCount, allVisibleSelected,
    deleteTargetLabel, modalTitle, investmentTypes, form, bulkRows, onSort, abrirNovo, abrirEdicao, salvar,
    abrirExclusao, abrirExclusaoSelecionadas, confirmarExclusao, abrirAdicaoEmMassa, addBulkRow, removeBulkRow,
    salvarEmMassa, toggleSelection, toggleSelectAllVisible, carregar,
} = useInvestments(toast);

function formatBrl(v) {
    if (v === null || v === undefined || v === '') return '—';
    return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}
function formatDate(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString('pt-BR');
}
function labelType(type) {
    return investmentTypes.find((x) => x.value === type)?.label ?? type;
}

onMounted(async () => {
    await carregar();
});
</script>
