<template>
    <div>
        <h1 class="text-2xl font-semibold text-white">
            Previsão de caixa
        </h1>
        <p class="mt-1 text-slate-400">
            Saldo projetado dia a dia (entradas antes de saídas no mesmo dia).
        </p>

        <div class="dz-sticky-filter sticky top-[57px] z-10 -mx-4 mt-4 flex flex-wrap items-end gap-4 border-y border-slate-800 bg-slate-950/92 px-4 py-3 backdrop-blur md:static md:mx-0 md:mt-6 md:border-0 md:bg-transparent md:p-0">
            <div>
                <label class="text-xs text-slate-500">Conta</label>
                <select
                    v-model="accountId"
                    class="dz-select mt-1 min-w-[200px]"
                    @change="carregar()"
                >
                    <option value="">
                        Todas (consolidado)
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
                <label class="text-xs text-slate-500">Data inicial</label>
                <DzDatePicker
                    v-model="fromDate"
                    class="mt-1 block min-w-[200px]"
                    input-class="w-full min-w-[200px]"
                    :min-date="projectionLimits.minFrom"
                    :max-date="projectionLimits.maxFrom"
                    @update:model-value="onFromDatePicked"
                />
            </div>
            <div>
                <label class="text-xs text-slate-500">Data final</label>
                <DzDatePicker
                    v-model="toDate"
                    class="mt-1 block min-w-[200px]"
                    input-class="w-full min-w-[200px]"
                    :min-date="projectionLimits.minTo"
                    :max-date="projectionLimits.maxTo"
                    @update:model-value="onToDatePicked"
                />
            </div>
            <button type="button" class="dz-btn dz-btn-ghost" @click="carregar()">
                Atualizar
            </button>
            <div class="w-full">
                <div class="mt-1 flex flex-wrap gap-2">
                    <button type="button" class="dz-btn text-xs" :class="quickRangeActive === 'today' ? 'dz-btn-primary' : 'dz-btn-ghost'" @click="setQuickRangeToday">Hoje</button>
                    <button type="button" class="dz-btn text-xs" :class="quickRangeActive === 'tomorrow' ? 'dz-btn-primary' : 'dz-btn-ghost'" @click="setQuickRangeTomorrow">Amanhã</button>
                    <button type="button" class="dz-btn text-xs" :class="quickRangeActive === 'next7' ? 'dz-btn-primary' : 'dz-btn-ghost'" @click="setQuickRangeNext7">Próx. 7 dias</button>
                    <button type="button" class="dz-btn text-xs" :class="quickRangeActive === 'currentMonth' ? 'dz-btn-primary' : 'dz-btn-ghost'" @click="setQuickRangeCurrentMonth">Mês atual</button>
                    <button type="button" class="dz-btn text-xs" :class="quickRangeActive === 'nextMonth' ? 'dz-btn-primary' : 'dz-btn-ghost'" @click="setQuickRangeNextMonth">Próx. mês</button>
                </div>
            </div>
        </div>

        <Transition
            mode="out-in"
            name="page"
        >
            <AppSkeleton v-if="loading" key="load" class="mt-8" />

            <div
                v-else
                key="data"
            >
                <div class="mt-6 rounded-xl border border-slate-800 bg-slate-900/30 p-4">
                    <div class="flex flex-wrap items-end gap-3">
                        <div>
                            <label class="text-xs text-slate-500">Meta de caixa mínimo</label>
                            <input
                                v-model.number="cashFloor"
                                type="number"
                                min="0"
                                step="0.01"
                                class="mt-1 rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                                @change="persistCashFloor"
                            >
                        </div>
                        <p class="text-xs" :class="belowFloorCount > 0 ? 'text-rose-300' : 'text-emerald-300'">
                            {{ belowFloorCount > 0 ? `${belowFloorCount} dia(s) abaixo da meta` : 'Nenhum dia abaixo da meta' }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="summary.disclaimer"
                    class="mt-6 rounded-lg border border-amber-900/50 bg-amber-950/20 px-4 py-3 text-sm text-amber-200/90"
                >
                    {{ summary.disclaimer }}
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div
                        class="rounded-xl border border-emerald-900/40 bg-emerald-950/20 p-4 transition-transform duration-300 hover:-translate-y-0.5"
                    >
                        <p class="text-xs uppercase text-emerald-500/90">
                            Saldo inicial (início do período)
                        </p>
                        <p class="mt-1 text-xl font-semibold text-white">
                            {{ formatBrl(summary.opening_balance_consolidated) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Antes dos lançamentos do dia {{ projectionStart ? formatDate(projectionStart) : '—' }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sky-900/40 bg-sky-950/20 p-4 transition-transform duration-300 hover:-translate-y-0.5"
                    >
                        <p class="text-xs uppercase text-sky-400/90">
                            Saldo projetado (fim do 1º dia)
                        </p>
                        <p class="mt-1 text-xl font-semibold text-white">
                            {{ formatBrl(summary.current_balance) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Após movimentos em {{ summary.current_balance_date ? formatDate(summary.current_balance_date) : '—' }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-900/40 p-4 transition-transform duration-300 hover:-translate-y-0.5"
                    >
                        <p class="text-xs uppercase text-slate-500">
                            Menor saldo no período
                        </p>
                        <p
                            class="mt-1 text-xl font-semibold"
                            :class="Number(summary.minimum_balance) < 0 ? 'text-rose-400' : 'text-white'"
                        >
                            {{ formatBrl(summary.minimum_balance) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ summary.minimum_balance_date ? formatDate(summary.minimum_balance_date) : '—' }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-900/40 p-4 transition-transform duration-300 hover:-translate-y-0.5"
                    >
                        <p class="text-xs uppercase text-slate-500">
                            Primeiro dia negativo
                        </p>
                        <p
                            class="mt-1 text-lg font-medium"
                            :class="summary.first_negative_date ? 'text-rose-400' : 'text-emerald-400'"
                        >
                            {{
                                summary.first_negative_date
                                    ? formatDate(summary.first_negative_date)
                                    : 'Nenhum'
                            }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-slate-800 bg-slate-900/40 p-4 transition-transform duration-300 hover:-translate-y-0.5"
                    >
                        <p class="text-xs uppercase text-slate-500">
                            Período
                        </p>
                        <p class="mt-1 text-sm text-slate-300">
                            {{ formatDate(projectionStart) }} — {{ formatDate(projectionEnd) }}
                        </p>
                        <div class="mt-2 space-y-1">
                            <p class="text-xs text-emerald-300">
                                Entradas: {{ formatBrl(summary.income_total) }}
                            </p>
                            <p class="text-xs text-rose-300">
                                Saídas: {{ formatBrl(summary.expense_total) }}
                            </p>
                            <p
                                class="text-xs"
                                :class="Number(summary.net_cash_flow_total) >= 0 ? 'text-emerald-300' : 'text-rose-300'"
                            >
                                Sobra líquida (caixa): {{ formatBrl(summary.net_cash_flow_total) }}
                            </p>
                            <p class="mt-2 border-t border-slate-800 pt-2 text-[11px] text-slate-500">
                                Investimentos (mesmo período)
                            </p>
                            <p class="text-xs text-slate-300">
                                Aportes (saída de caixa): {{ formatBrl(summary.investment_contributions_cash_total ?? 0) }}
                            </p>
                            <p class="text-xs text-sky-300">
                                Rendimento estimado (juros compostos): {{ formatBrl(summary.investment_estimated_yield_period ?? 0) }}
                            </p>
                            <p
                                class="text-xs font-medium"
                                :class="Number(summary.net_economic_including_investments ?? 0) >= 0 ? 'text-emerald-300' : 'text-rose-300'"
                            >
                                Sobra + rend. invest. estimado: {{ formatBrl(summary.net_economic_including_investments ?? 0) }}
                            </p>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4 transition-transform duration-300 hover:-translate-y-0.5">
                        <p class="text-xs uppercase text-slate-500">Investimentos no fim</p>
                        <p class="mt-1 text-lg font-medium text-emerald-300">{{ formatBrl(projectedInvestmentsEnd) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-4 transition-transform duration-300 hover:-translate-y-0.5">
                        <p class="text-xs uppercase text-slate-500">Patrimônio estimado</p>
                        <p class="mt-1 text-lg font-medium text-slate-100">{{ formatBrl(projectedNetWorthEnd) }}</p>
                    </div>
                </div>

                <div
                    v-if="chartLabels.length"
                    class="mt-8 rounded-xl border border-slate-800 bg-slate-900/30 p-4 transition-shadow duration-300 hover:shadow-lg hover:shadow-emerald-950/10"
                >
                    <h2 class="mb-2 text-sm font-medium text-slate-400">
                        Curva de saldo
                    </h2>
                    <ProjectionLineChart
                        :labels="chartLabels"
                        :values="chartValues"
                        :detail-days="days"
                        title="Saldo fim do dia"
                        @day-click="abrirDetalheDia"
                    />
                </div>

                <div class="mt-6 rounded-xl border border-slate-800 bg-slate-900/30 p-4">
                    <h2 class="mb-2 text-sm font-medium text-slate-400">
                        Visão compacta
                    </h2>
                    <div
                        v-if="compactDays.length"
                        class="overflow-x-auto"
                    >
                        <svg
                            :width="Math.max(640, compactDays.length * 18)"
                            height="120"
                            class="block min-w-full"
                        >
                            <polyline
                                fill="none"
                                stroke="#34d399"
                                stroke-width="2"
                                :points="compactPolylinePoints"
                            />
                            <g
                                v-for="(d, idx) in compactDays"
                                :key="`cp-${d.date}`"
                            >
                                <circle
                                    :cx="compactX(idx)"
                                    :cy="compactY(balanceForDay(d))"
                                    r="3"
                                    fill="#10b981"
                                    class="cursor-pointer"
                                    @click="abrirDetalheDia(d)"
                                />
                            </g>
                        </svg>
                    </div>
                    <p
                        v-else
                        class="text-sm text-slate-500"
                    >
                        Sem dados de projeção para o período selecionado.
                    </p>
                </div>

                <div class="mt-6 rounded-xl border border-slate-800 bg-slate-900/30 p-4">
                    <h2 class="text-sm font-medium text-slate-300">
                        Próximos 7 dias críticos
                    </h2>
                    <div class="mt-3 space-y-2">
                        <div
                            v-for="d in nextCriticalDays"
                            :key="`critical-${d.date}`"
                            class="rounded-lg border border-slate-800 bg-slate-950/50 px-3 py-2 text-xs"
                        >
                            <span class="text-slate-200">{{ formatDate(d.date) }}</span>
                            <span class="ml-2" :class="statusLabel(d) === 'Risco' ? 'text-rose-300' : 'text-amber-300'">
                                {{ statusLabel(d) }}
                            </span>
                            <span class="ml-2 text-slate-400">Saldo: {{ formatBrl(balanceForDay(d)) }}</span>
                        </div>
                        <p v-if="!nextCriticalDays.length" class="text-xs text-emerald-300">
                            Sem dias críticos no horizonte selecionado.
                        </p>
                    </div>
                </div>

                <div class="mt-8 overflow-x-auto rounded-xl border border-slate-800">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-800 bg-slate-900/60 text-xs uppercase text-slate-500">
                            <tr>
                                <SortableTh
                                    column="date"
                                    model-key="date"
                                    :model-dir="projectionSortDir"
                                    @sort="onProjectionTableSort"
                                >
                                    Data
                                </SortableTh>
                                <th class="px-4 py-3">
                                    Semáforo
                                </th>
                                <th class="px-4 py-3">
                                    Saldo fim do dia
                                </th>
                                <th class="px-4 py-3">
                                    Movimentos
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="d in sortedTableDays"
                                :key="d.date"
                                class="border-b border-slate-800/80 transition-colors duration-150 hover:bg-slate-800/20"
                                :class="Number(balanceForDay(d)) < 0 ? 'bg-rose-950/15' : ''"
                            >
                                <td class="px-4 py-3 text-slate-300">
                                    {{ formatDate(d.date) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs" :class="statusPillClass(d)">
                                        {{ statusLabel(d) }}
                                    </span>
                                </td>
                                <td
                                    class="px-4 py-3 font-medium"
                                    :class="Number(balanceForDay(d)) < 0 ? 'text-rose-400' : 'text-white'"
                                >
                                    {{ formatBrl(balanceForDay(d)) }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-400">
                                    <span v-if="!d.movements?.length">—</span>
                                    <span
                                        v-else
                                        class="line-clamp-2"
                                    >{{ movementSummary(d.movements) }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </Transition>

        <Modal
            v-model:show="detailModalOpen"
            title="Detalhes do dia"
        >
            <template v-if="selectedDay">
                <p class="text-sm text-slate-400">
                    {{ formatDate(selectedDay.date) }}
                </p>
                <p
                    class="mt-2 text-lg font-semibold"
                    :class="Number(balanceForDay(selectedDay)) < 0 ? 'text-rose-300' : 'text-emerald-300'"
                >
                    Saldo no fim do dia: {{ formatBrl(balanceForDay(selectedDay)) }}
                </p>
                <div class="mt-4 rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                    <p class="text-xs uppercase text-slate-500">
                        Entradas
                    </p>
                    <div class="mt-2 space-y-1 text-sm text-slate-300">
                        <p v-if="!incomeMoves.length">—</p>
                        <p v-for="(m, idx) in incomeMoves" :key="`in-${idx}`">
                            + {{ movementLabel(m) }} ({{ formatBrl(m.signed_amount) }})
                        </p>
                    </div>
                </div>
                <div class="mt-3 rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                    <p class="text-xs uppercase text-slate-500">
                        Débitos
                    </p>
                    <div class="mt-2 space-y-1 text-sm text-slate-300">
                        <p v-if="!expenseMoves.length">—</p>
                        <p v-for="(m, idx) in expenseMoves" :key="`out-${idx}`">
                            - {{ movementLabel(m) }} ({{ formatBrl(m.signed_amount) }})
                        </p>
                    </div>
                </div>
            </template>
        </Modal>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import AppSkeleton from '../components/AppSkeleton.vue';
import DzDatePicker from '../components/DzDatePicker.vue';
import Modal from '../components/Modal.vue';
import ProjectionLineChart from '../components/ProjectionLineChart.vue';
import SortableTh from '../components/SortableTh.vue';
import { useProjection } from '../composables/useProjection';
import { projectionDatePickerLimits } from '../utils/dateRange';

const {
    loading,
    projectionSortDir,
    accounts,
    accountId,
    summary,
    days,
    sortedTableDays,
    projectionStart,
    projectionEnd,
    fromDate,
    toDate,
    projectedInvestmentsEnd,
    projectedNetWorthEnd,
    chartLabels,
    chartValues,
    onProjectionTableSort,
    balanceForDay,
    carregar,
    init,
    setFromDate,
    setToDate,
    setDateRange,
} = useProjection();

const projectionLimits = computed(() => projectionDatePickerLimits(fromDate.value, toDate.value));

function onFromDatePicked() {
    setFromDate(fromDate.value);
    carregar();
}

function onToDatePicked() {
    setToDate(toDate.value);
    carregar();
}

function localIso(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function applyQuickRange(from, to) {
    setDateRange(from, to);
    carregar();
}

function setQuickRangeToday() {
    const now = new Date();
    const iso = localIso(now);
    applyQuickRange(iso, iso);
}

function setQuickRangeTomorrow() {
    const now = new Date();
    now.setDate(now.getDate() + 1);
    const iso = localIso(now);
    applyQuickRange(iso, iso);
}

function setQuickRangeNext7() {
    const now = new Date();
    const from = localIso(now);
    now.setDate(now.getDate() + 6);
    const to = localIso(now);
    applyQuickRange(from, to);
}

function setQuickRangeCurrentMonth() {
    const now = new Date();
    const start = new Date(now.getFullYear(), now.getMonth(), 1);
    const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    applyQuickRange(localIso(start), localIso(end));
}

function setQuickRangeNextMonth() {
    const now = new Date();
    const start = new Date(now.getFullYear(), now.getMonth() + 1, 1);
    const end = new Date(now.getFullYear(), now.getMonth() + 2, 0);
    applyQuickRange(localIso(start), localIso(end));
}

const quickRangeActive = computed(() => {
    const from = fromDate.value;
    const to = toDate.value;
    if (!from || !to) return '';

    const now = new Date();
    const today = localIso(now);

    const tomorrowDate = new Date(now);
    tomorrowDate.setDate(tomorrowDate.getDate() + 1);
    const tomorrow = localIso(tomorrowDate);

    const next7EndDate = new Date(now);
    next7EndDate.setDate(next7EndDate.getDate() + 6);
    const next7End = localIso(next7EndDate);

    const monthStart = localIso(new Date(now.getFullYear(), now.getMonth(), 1));
    const monthEnd = localIso(new Date(now.getFullYear(), now.getMonth() + 1, 0));

    const nextMonthStart = localIso(new Date(now.getFullYear(), now.getMonth() + 1, 1));
    const nextMonthEnd = localIso(new Date(now.getFullYear(), now.getMonth() + 2, 0));

    if (from === today && to === today) return 'today';
    if (from === tomorrow && to === tomorrow) return 'tomorrow';
    if (from === today && to === next7End) return 'next7';
    if (from === monthStart && to === monthEnd) return 'currentMonth';
    if (from === nextMonthStart && to === nextMonthEnd) return 'nextMonth';

    return '';
});

const detailModalOpen = ref(false);
const selectedDay = ref(null);
const compactHeight = 120;
const compactPadding = 10;
const cashFloor = ref(1000);

const incomeMoves = computed(() =>
    (selectedDay.value?.movements ?? []).filter((m) => Number(m.signed_amount) > 0),
);
const expenseMoves = computed(() =>
    (selectedDay.value?.movements ?? []).filter((m) => Number(m.signed_amount) < 0),
);
const compactDays = computed(() => {
    const source = Array.isArray(days.value) ? days.value : [];
    if (source.length <= 40) {
        return source;
    }
    const step = Math.ceil(source.length / 40);
    return source.filter((_, idx) => idx % step === 0);
});
const compactMin = computed(() => {
    if (!compactDays.value.length) return 0;
    return Math.min(...compactDays.value.map((d) => Number(balanceForDay(d))));
});
const compactMax = computed(() => {
    if (!compactDays.value.length) return 0;
    return Math.max(...compactDays.value.map((d) => Number(balanceForDay(d))));
});
const compactSpan = computed(() => {
    const span = compactMax.value - compactMin.value;
    return span <= 0 ? 1 : span;
});
const compactPolylinePoints = computed(() =>
    compactDays.value
        .map((d, idx) => `${compactX(idx)},${compactY(balanceForDay(d))}`)
        .join(' '),
);
const belowFloorCount = computed(() =>
    days.value.filter((d) => Number(balanceForDay(d)) < Number(cashFloor.value)).length,
);
const nextCriticalDays = computed(() =>
    days.value
        .filter((d) => statusLabel(d) !== 'OK')
        .slice(0, 7),
);

function formatBrl(v) {
    if (v === null || v === undefined) {
        return '—';
    }
    return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function formatDate(d) {
    if (! d) {
        return '';
    }
    return new Date(d + 'T12:00:00').toLocaleDateString('pt-BR');
}

function movementSummary(movements) {
    return movements
        .map((m) => `${movementLabel(m)} (${Number(m.signed_amount) >= 0 ? '+' : ''}${Number(m.signed_amount).toFixed(2)})`)
        .join('; ');
}

function movementLabel(m) {
    const raw = String(m?.description ?? '').trim();
    if (!raw) {
        return Number(m?.signed_amount) >= 0 ? 'Entrada' : 'Saída';
    }
    const lower = raw.toLowerCase();
    if (lower === 'income') return 'Entrada';
    if (lower === 'expense') return 'Saída';
    return raw;
}

function abrirDetalheDia(day) {
    selectedDay.value = day;
    detailModalOpen.value = true;
}

function statusLabel(d) {
    const balance = Number(balanceForDay(d));
    const floor = Number(cashFloor.value);
    if (balance < floor) return 'Risco';
    if (balance < floor * 1.3) return 'Atenção';
    return 'OK';
}

function statusPillClass(d) {
    const label = statusLabel(d);
    if (label === 'Risco') return 'bg-rose-900/50 text-rose-200';
    if (label === 'Atenção') return 'bg-amber-900/50 text-amber-200';
    return 'bg-emerald-900/50 text-emerald-200';
}

function persistCashFloor() {
    const v = Number(cashFloor.value);
    const safe = Number.isFinite(v) && v >= 0 ? v : 0;
    cashFloor.value = safe;
    localStorage.setItem('dz_projection_cash_floor', String(safe));
}

function compactX(idx) {
    return compactPadding + (idx * 18);
}

function compactY(v) {
    const n = Number(v);
    const normalized = (n - compactMin.value) / compactSpan.value;
    const drawable = compactHeight - (compactPadding * 2);
    return compactHeight - compactPadding - (normalized * drawable);
}

onMounted(async () => {
    const saved = Number(localStorage.getItem('dz_projection_cash_floor') ?? '1000');
    if (Number.isFinite(saved) && saved >= 0) {
        cashFloor.value = saved;
    }
    await init();
});
</script>
