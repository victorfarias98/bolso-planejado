<template>
    <div>
        <h1 class="text-2xl font-semibold text-white">Recomendações gerais</h1>
        <p class="mt-1 text-slate-400">Sugestões práticas para reduzir gastos por categoria, comparar meses e definir ações semanais.</p>

        <div class="dz-sticky-filter sticky top-[57px] z-10 -mx-4 mt-4 border-y border-slate-800 bg-slate-950/92 px-4 py-3 backdrop-blur md:static md:mx-0 md:border-0 md:bg-transparent md:p-0">
            <div class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="text-xs text-slate-500">De</label>
                    <DzDatePicker
                        v-model="filters.from"
                        class="mt-1 block min-w-[180px]"
                        input-class="w-full min-w-[180px]"
                        :min-date="filterLimits.minFrom"
                        :max-date="filterLimits.maxFrom"
                        @update:model-value="agendarRecarga"
                    />
                </div>
                <div>
                    <label class="text-xs text-slate-500">Até</label>
                    <DzDatePicker
                        v-model="filters.to"
                        class="mt-1 block min-w-[180px]"
                        input-class="w-full min-w-[180px]"
                        :min-date="filterLimits.minTo"
                        :max-date="filterLimits.maxTo"
                        @update:model-value="agendarRecarga"
                    />
                </div>
                <button type="button" class="dz-btn dz-btn-ghost" @click="carregar()">Atualizar análise</button>
            </div>
            <div class="mt-2 flex flex-wrap gap-2">
                <button type="button" class="dz-btn text-xs" :class="activePreset === 'currentMonth' ? 'dz-btn-primary' : 'dz-btn-ghost'" @click="setPeriodCurrentMonth">Mês atual</button>
                <button type="button" class="dz-btn text-xs" :class="activePreset === 'nextMonth' ? 'dz-btn-primary' : 'dz-btn-ghost'" @click="setPeriodNextMonth">Próx. mês</button>
                <button type="button" class="dz-btn text-xs" :class="activePreset === 'last7' ? 'dz-btn-primary' : 'dz-btn-ghost'" @click="setPeriodLast7">Últ. 7 dias</button>
                <button type="button" class="dz-btn text-xs" :class="activePreset === 'last30' ? 'dz-btn-primary' : 'dz-btn-ghost'" @click="setPeriodLast30">Últ. 30 dias</button>
                <button type="button" class="dz-btn text-xs" :class="activePreset === 'today' ? 'dz-btn-primary' : 'dz-btn-ghost'" @click="setPeriodToday">Hoje</button>
            </div>
            <p
                v-if="payload?.period"
                class="mt-2 text-xs text-slate-500"
            >
                Período aplicado: {{ formatDate(payload.period.from) }} até {{ formatDate(payload.period.to) }}
            </p>
        </div>

        <AppSkeleton v-if="loading" class="mt-8" />
        <div v-else-if="loadError" class="mt-8 rounded-lg border border-rose-900/60 bg-rose-950/30 px-4 py-3 text-sm text-rose-200">
            <p>{{ loadError }}</p>
            <button type="button" class="dz-btn dz-btn-ghost mt-3" @click="carregar()">Tentar novamente</button>
        </div>

        <div v-else-if="payload" class="mt-6 space-y-6">
            <div class="grid gap-3 sm:grid-cols-5">
                <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4"><p class="text-xs uppercase text-slate-500">Entradas</p><p class="mt-1 text-lg font-semibold text-emerald-300">{{ formatBrl(payload.summary.income) }}</p></div>
                <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4"><p class="text-xs uppercase text-slate-500">Saídas</p><p class="mt-1 text-lg font-semibold text-rose-300">{{ formatBrl(payload.summary.expense) }}</p></div>
                <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4"><p class="text-xs uppercase text-slate-500">Sobra mensal</p><p class="mt-1 text-lg font-semibold text-white">{{ formatBrl(payload.summary.surplus) }}</p></div>
                <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4"><p class="text-xs uppercase text-slate-500">Dívida ativa</p><p class="mt-1 text-lg font-semibold text-slate-100">{{ formatBrl(payload.summary.debt_balance_active) }}</p></div>
                <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4"><p class="text-xs uppercase text-slate-500">Investido</p><p class="mt-1 text-lg font-semibold text-slate-100">{{ formatBrl(payload.summary.invested_total) }}</p></div>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/30 p-4">
                <h2 class="text-sm font-medium text-slate-300">Comparativo mensal (M vs M-1)</h2>
                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg border border-slate-800 bg-slate-950/50 p-3"><p class="text-xs uppercase text-slate-500">Entradas</p><p class="mt-1 text-sm text-white">Atual: {{ formatBrl(payload.monthly_comparison.current.income) }}</p><p class="text-xs text-slate-400">M-1: {{ formatBrl(payload.monthly_comparison.previous.income) }}</p></div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/50 p-3"><p class="text-xs uppercase text-slate-500">Saídas</p><p class="mt-1 text-sm text-white">Atual: {{ formatBrl(payload.monthly_comparison.current.expense) }}</p><p class="text-xs text-slate-400">M-1: {{ formatBrl(payload.monthly_comparison.previous.expense) }}</p></div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/50 p-3"><p class="text-xs uppercase text-slate-500">Sobra</p><p class="mt-1 text-sm text-white">Atual: {{ formatBrl(payload.monthly_comparison.current.surplus) }}</p><p class="text-xs text-slate-400">M-1: {{ formatBrl(payload.monthly_comparison.previous.surplus) }}</p></div>
                </div>
                <div class="mt-3 space-y-2">
                    <p class="text-xs uppercase text-slate-500">Top categorias (variação)</p>
                    <div v-for="(c, idx) in payload.monthly_comparison.categories_delta" :key="idx" class="rounded-lg border border-slate-800 bg-slate-950/50 p-2 text-xs">
                        <span class="text-slate-200">{{ c.category }}</span>
                        <span class="ml-2 text-slate-400">Atual {{ formatBrl(c.current_amount) }} / M-1 {{ formatBrl(c.previous_amount) }}</span>
                        <span class="ml-2" :class="c.direction === 'up' ? 'text-rose-300' : (c.direction === 'down' ? 'text-emerald-300' : 'text-slate-400')">{{ c.direction === 'up' ? 'Subiu' : (c.direction === 'down' ? 'Caiu' : 'Estável') }} ({{ formatBrl(c.delta_amount) }})</span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/30 p-4">
                <h2 class="text-sm font-medium text-slate-300">Metas de corte por categoria</h2>
                <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="w-full sm:max-w-xs">
                        <label class="text-xs text-slate-500">Categoria</label>
                        <div class="mt-1 flex gap-2">
                            <select v-model="goalForm.category_id" class="min-w-0 flex-1 rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"><option value="">Selecione</option><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option></select>
                            <button type="button" class="shrink-0 rounded-lg border border-slate-700 px-2 py-2 text-xs text-slate-300 hover:bg-slate-800 whitespace-nowrap" @click="abrirNovaCategoria">Nova</button>
                        </div>
                    </div>
                    <div class="w-full sm:max-w-[180px]"><label class="text-xs text-slate-500">Limite mensal</label><input v-model="goalForm.monthly_limit" type="number" step="0.01" min="0.01" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"></div>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500 disabled:opacity-50" :disabled="goalSaving" @click="salvarMeta">{{ goalSaving ? 'Salvando…' : 'Salvar meta' }}</button>
                </div>
                <p v-if="goalError" class="mt-2 text-sm text-rose-400">{{ goalError }}</p>
                <div class="mt-3 space-y-3">
                    <div v-for="g in payload.category_goals_progress" :key="g.goal_id" class="rounded-lg border border-slate-800 bg-slate-950/50 p-3">
                        <div class="flex items-center justify-between"><p class="text-sm font-medium text-white">{{ g.category_name }}</p><button type="button" class="text-xs text-rose-400 hover:text-rose-300" @click="excluirMeta(g.goal_id)">Remover meta</button></div>
                        <p class="mt-1 text-xs text-slate-400">Gasto atual: {{ formatBrl(g.current_spent) }} / Limite: {{ formatBrl(g.monthly_limit) }} ({{ g.progress_pct }}%)</p>
                        <p class="mt-1 text-xs" :class="g.status === 'over_limit' ? 'text-rose-300' : 'text-emerald-300'">{{ g.status === 'over_limit' ? 'Acima do limite: reavaliar esta categoria agora.' : 'Dentro da meta no mês atual.' }}</p>
                    </div>
                    <p v-if="!payload.category_goals_progress?.length" class="text-sm text-slate-500">Cadastre metas para acompanhar o progresso por categoria.</p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/30 p-4">
                <h2 class="text-sm font-medium text-slate-300">Simulador de decisão</h2>
                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div><label class="text-xs text-slate-500">Corte mensal adicional</label><input v-model="sim.cut_amount" type="number" min="0" step="0.01" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"></div>
                    <div><label class="text-xs text-slate-500">Aporte mensal adicional</label><input v-model="sim.extra_investment" type="number" min="0" step="0.01" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"></div>
                    <div><label class="text-xs text-slate-500">Parcela para amortizar dívida (%)</label><input v-model.number="sim.debt_share_pct" type="number" min="0" max="100" step="1" class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"></div>
                </div>
                <div class="mt-3 rounded-lg border border-slate-800 bg-slate-950/50 p-3 text-sm text-slate-300">
                    <p>Nova sobra estimada: <span class="text-white">{{ formatBrl(simulation.new_surplus) }}</span></p>
                    <p>Amortização mensal estimada: <span class="text-rose-300">{{ formatBrl(simulation.debt_payment) }}</span></p>
                    <p>Aporte mensal total estimado: <span class="text-emerald-300">{{ formatBrl(simulation.investment_total) }}</span></p>
                    <p>Prazo de quitação estimado: <span class="text-white">{{ simulation.debt_payoff_months === null ? 'Sem previsão' : `${simulation.debt_payoff_months} meses` }}</span></p>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500 disabled:opacity-50" :disabled="applySaving" @click="aplicarPlano">
                        {{ applySaving ? 'Aplicando…' : 'Aplicar plano' }}
                    </button>
                    <p v-if="applyMessage" class="text-xs text-emerald-300">{{ applyMessage }}</p>
                    <p v-if="applyError" class="text-xs text-rose-300">{{ applyError }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/30 p-4">
                <h2 class="text-sm font-medium text-slate-300">Categorias para atacar primeiro</h2>
                <div class="mt-3 space-y-3">
                    <div v-for="(item, idx) in payload.category_recommendations" :key="idx" class="rounded-lg border border-slate-800 bg-slate-950/50 p-3">
                        <p class="text-sm font-medium text-white">{{ item.category }} - {{ formatBrl(item.amount) }} ({{ item.expense_share_pct }}% dos gastos)</p>
                        <p class="mt-1 text-xs text-slate-400">{{ item.message }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-800 bg-slate-900/30 p-4">
                    <h2 class="text-sm font-medium text-slate-300">Recomendação de aporte</h2>
                    <p class="mt-2 text-lg font-semibold text-emerald-300">{{ formatBrl(payload.investment_recommendation.suggested_monthly_investment) }}/mês</p>
                    <p class="mt-1 text-xs text-slate-400">{{ payload.investment_recommendation.message }}</p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-900/30 p-4">
                    <h2 class="text-sm font-medium text-slate-300">Plano de ação geral</h2>
                    <ul class="mt-2 space-y-1 text-sm text-slate-300"><li v-for="(item, idx) in payload.general_recommendations" :key="idx">- {{ item }}</li></ul>
                </div>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/30 p-4">
                <h2 class="text-sm font-medium text-slate-300">Plano semanal sugerido</h2>
                <div class="mt-3 space-y-2">
                    <div v-for="(task, idx) in payload.weekly_plan" :key="idx" class="rounded-lg border border-slate-800 bg-slate-950/50 p-3">
                        <p class="text-sm font-medium text-white">{{ task.day }} - {{ task.title }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ task.detail }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/30 p-4">
                <h2 class="text-sm font-medium text-slate-300">Histórico de planos aplicados</h2>
                <div class="mt-3 space-y-2">
                    <div v-for="h in planHistories" :key="h.id" class="rounded-lg border border-slate-800 bg-slate-950/50 p-3 text-xs text-slate-300">
                        <p class="text-slate-100">{{ new Date(h.created_at).toLocaleString('pt-BR') }}</p>
                        <p>Categoria foco: {{ h.category_name || '—' }}</p>
                        <p>Corte: {{ formatBrl(h.cut_amount) }} | Extra aporte: {{ formatBrl(h.extra_investment) }} | Dívida: {{ Number(h.debt_share_pct).toFixed(0) }}%</p>
                        <p>Amortização simulada: {{ formatBrl(h.simulated_debt_payment) }} | Aporte total simulado: {{ formatBrl(h.simulated_investment_total) }}</p>
                        <p>Prazo de quitação simulado: {{ h.simulated_debt_payoff_months ?? 'Sem previsão' }}</p>
                    </div>
                    <p v-if="!planHistories.length" class="text-sm text-slate-500">Nenhum plano aplicado ainda.</p>
                </div>
            </div>
        </div>

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
                <p v-if="newCategoryError" class="mt-2 text-sm text-rose-400">{{ newCategoryError }}</p>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <button type="button" class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300 hover:bg-slate-800" @click="newCategoryModalOpen = false">Cancelar</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-500 disabled:opacity-50" :disabled="creatingCategory" @click="salvarNovaCategoria">{{ creatingCategory ? 'Salvando…' : 'Criar' }}</button>
                </div>
            </template>
        </Modal>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import AppSkeleton from '../components/AppSkeleton.vue';
import DzDatePicker from '../components/DzDatePicker.vue';
import Modal from '../components/Modal.vue';
import { createCategory } from '../api/categories';
import { http } from '../api/http';
import { useToastStore } from '../stores/toast';
import { projectionDatePickerLimits } from '../utils/dateRange';

const loading = ref(true);
const payload = ref(null);
const loadError = ref('');
const categories = ref([]);
const planHistories = ref([]);
const goalSaving = ref(false);
const goalError = ref('');
const applySaving = ref(false);
const applyMessage = ref('');
const applyError = ref('');
const toast = useToastStore();
const goalForm = reactive({ category_id: '', monthly_limit: '' });
const newCategoryModalOpen = ref(false);
const newCategoryName = ref('');
const creatingCategory = ref(false);
const newCategoryError = ref('');
const sim = reactive({ cut_amount: 0, extra_investment: 0, debt_share_pct: 70 });
const filters = reactive({
    from: localDate(new Date(new Date().getFullYear(), new Date().getMonth(), 1)),
    to: localDate(new Date()),
});

const filterLimits = computed(() => projectionDatePickerLimits(filters.from, filters.to));
let loadSeq = 0;
let reloadTimer = null;

function formatBrl(v) {
    if (v === null || v === undefined || v === '') return '—';
    return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function formatDate(v) {
    if (!v) return '—';
    return new Date(v + 'T12:00:00').toLocaleDateString('pt-BR');
}

function localDate(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
}

function applyPeriod(from, to) {
    filters.from = from;
    filters.to = to;
    carregar();
}

function setPeriodToday() {
    const now = new Date();
    const iso = localDate(now);
    applyPeriod(iso, iso);
}

function setPeriodLast7() {
    const end = new Date();
    const start = new Date();
    start.setDate(start.getDate() - 6);
    applyPeriod(localDate(start), localDate(end));
}

function setPeriodLast30() {
    const end = new Date();
    const start = new Date();
    start.setDate(start.getDate() - 29);
    applyPeriod(localDate(start), localDate(end));
}

function setPeriodCurrentMonth() {
    const now = new Date();
    const start = new Date(now.getFullYear(), now.getMonth(), 1);
    const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    applyPeriod(localDate(start), localDate(end));
}

function setPeriodNextMonth() {
    const now = new Date();
    const start = new Date(now.getFullYear(), now.getMonth() + 1, 1);
    const end = new Date(now.getFullYear(), now.getMonth() + 2, 0);
    applyPeriod(localDate(start), localDate(end));
}

const activePreset = computed(() => {
    const from = filters.from;
    const to = filters.to;
    if (!from || !to) return '';

    const now = new Date();
    const today = localDate(now);

    const last7Start = new Date(now);
    last7Start.setDate(last7Start.getDate() - 6);
    const last7 = localDate(last7Start);

    const last30Start = new Date(now);
    last30Start.setDate(last30Start.getDate() - 29);
    const last30 = localDate(last30Start);

    const monthStart = localDate(new Date(now.getFullYear(), now.getMonth(), 1));
    const monthEnd = localDate(new Date(now.getFullYear(), now.getMonth() + 1, 0));

    const nextMonthStart = localDate(new Date(now.getFullYear(), now.getMonth() + 1, 1));
    const nextMonthEnd = localDate(new Date(now.getFullYear(), now.getMonth() + 2, 0));

    if (from === today && to === today) return 'today';
    if (from === last7 && to === today) return 'last7';
    if (from === last30 && to === today) return 'last30';
    if (from === monthStart && to === monthEnd) return 'currentMonth';
    if (from === nextMonthStart && to === nextMonthEnd) return 'nextMonth';

    return '';
});

async function carregar() {
    const requestId = ++loadSeq;
    loading.value = true;
    loadError.value = '';
    try {
        const { data } = await http.get('/recommendations', { params: { from: filters.from, to: filters.to } });
        if (requestId !== loadSeq) return;
        payload.value = data.data ?? null;
    } catch (e) {
        if (requestId !== loadSeq) return;
        const msg = e.response?.data?.message;
        loadError.value = msg || 'Não foi possível carregar recomendações.';
    } finally {
        if (requestId !== loadSeq) return;
        loading.value = false;
    }
}

function agendarRecarga() {
    if (reloadTimer) {
        clearTimeout(reloadTimer);
    }
    reloadTimer = setTimeout(() => {
        carregar();
    }, 220);
}

async function carregarCategorias() {
    try {
        const { data } = await http.get('/categories');
        categories.value = data.data ?? [];
    } catch {
        categories.value = [];
    }
}

function abrirNovaCategoria() {
    newCategoryName.value = '';
    newCategoryError.value = '';
    newCategoryModalOpen.value = true;
}

async function salvarNovaCategoria() {
    newCategoryError.value = '';
    const name = newCategoryName.value.trim();
    if (!name) {
        newCategoryError.value = 'Informe o nome da categoria.';
        return;
    }
    creatingCategory.value = true;
    try {
        const created = await createCategory({ name });
        await carregarCategorias();
        goalForm.category_id = created.id;
        newCategoryModalOpen.value = false;
        toast.success('Categoria criada.');
    } catch (e) {
        const errs = e.response?.data?.errors;
        newCategoryError.value = errs
            ? Object.values(errs).flat().join(' ')
            : e.response?.data?.message || 'Não foi possível criar a categoria.';
    } finally {
        creatingCategory.value = false;
    }
}

async function carregarHistoricoPlanos() {
    try {
        const { data } = await http.get('/plan-histories');
        planHistories.value = data.data ?? [];
    } catch {
        planHistories.value = [];
    }
}

async function salvarMeta() {
    goalError.value = '';
    if (!goalForm.category_id || !goalForm.monthly_limit) {
        goalError.value = 'Selecione a categoria e informe o limite mensal.';
        return;
    }
    goalSaving.value = true;
    try {
        await http.post('/category-goals', {
            category_id: goalForm.category_id,
            monthly_limit: goalForm.monthly_limit,
            is_active: true,
        });
        goalForm.category_id = '';
        goalForm.monthly_limit = '';
        await carregar();
    } catch (e) {
        const errs = e.response?.data?.errors;
        goalError.value = errs ? Object.values(errs).flat().join(' ') : 'Não foi possível salvar meta.';
    } finally {
        goalSaving.value = false;
    }
}

async function excluirMeta(goalId) {
    await http.delete(`/category-goals/${goalId}`);
    await carregar();
}

async function aplicarPlano() {
    applySaving.value = true;
    applyMessage.value = '';
    applyError.value = '';
    try {
        const topCategory = payload.value?.category_recommendations?.[0] ?? null;
        const cut = Number(sim.cut_amount || 0);
        const investMonthly = Number(simulation.value.investment_total || 0);

        if (topCategory?.category_id && cut > 0) {
            const currentAmount = Number(topCategory.amount || 0);
            const monthlyLimit = Math.max(1, currentAmount - cut).toFixed(2);
            await http.post('/category-goals', {
                category_id: topCategory.category_id,
                monthly_limit: monthlyLimit,
                is_active: true,
                notes: 'Meta criada automaticamente pelo simulador',
            });
        }

        if (investMonthly > 0) {
            const { data } = await http.get('/investments');
            const list = data.data ?? [];
            const existing = list.find((i) => i.title === 'Caixinha Plano Automático');
            if (existing) {
                await http.patch(`/investments/${existing.id}`, {
                    monthly_contribution: investMonthly.toFixed(2),
                    is_active: true,
                });
            } else {
                await http.post('/investments', {
                    title: 'Caixinha Plano Automático',
                    investment_type: 'pocket',
                    current_amount: '0.00',
                    monthly_contribution: investMonthly.toFixed(2),
                    monthly_return_rate: '0.30',
                    contribution_day: 5,
                    is_active: true,
                    notes: 'Criado automaticamente pelo simulador',
                });
            }
        }

        await http.post('/plan-histories', {
            category_id: topCategory?.category_id ?? null,
            cut_amount: Number(sim.cut_amount || 0).toFixed(2),
            extra_investment: Number(sim.extra_investment || 0).toFixed(2),
            debt_share_pct: Number(sim.debt_share_pct || 0).toFixed(2),
            simulated_debt_payment: Number(simulation.value.debt_payment || 0).toFixed(2),
            simulated_investment_total: Number(simulation.value.investment_total || 0).toFixed(2),
            simulated_debt_payoff_months: simulation.value.debt_payoff_months,
            notes: 'Plano aplicado pelo simulador',
        });

        applyMessage.value = 'Plano aplicado: meta de categoria e aporte mensal atualizados.';
        await Promise.all([carregar(), carregarHistoricoPlanos()]);
    } catch (e) {
        const errs = e.response?.data?.errors;
        applyError.value = errs ? Object.values(errs).flat().join(' ') : 'Não foi possível aplicar o plano.';
    } finally {
        applySaving.value = false;
    }
}

const simulation = computed(() => {
    const baseSurplus = Number(payload.value?.summary?.surplus ?? 0);
    const debtBalance = Number(payload.value?.summary?.debt_balance_active ?? 0);
    const baseInvest = Number(payload.value?.investment_recommendation?.suggested_monthly_investment ?? 0);
    const newSurplus = Math.max(0, baseSurplus + Number(sim.cut_amount || 0));
    const debtPayment = newSurplus * (Number(sim.debt_share_pct || 0) / 100);
    const investTotal = Math.max(0, baseInvest + Number(sim.extra_investment || 0) + (newSurplus - debtPayment));
    const payoff = debtPayment > 0 ? Math.ceil(debtBalance / debtPayment) : null;

    return {
        new_surplus: newSurplus.toFixed(2),
        debt_payment: debtPayment.toFixed(2),
        investment_total: investTotal.toFixed(2),
        debt_payoff_months: payoff,
    };
});

onMounted(async () => {
    await Promise.allSettled([
        carregarCategorias(),
        carregar(),
        carregarHistoricoPlanos(),
    ]);
});

onBeforeUnmount(() => {
    if (reloadTimer) {
        clearTimeout(reloadTimer);
        reloadTimer = null;
    }
});
</script>
