<template>
    <div>
        <h1 class="text-2xl font-semibold text-white">
            Olá, {{ auth.user?.name?.split(' ')[0] ?? '—' }}
        </h1>
        <p class="mt-1 text-slate-400">
            Resumo rápido da sua previsão de caixa.
        </p>

        <Transition
            mode="out-in"
            name="page"
        >
            <AppSkeleton v-if="loading" key="l" class="mt-8" />

            <div
                v-else-if="!accounts.length"
                key="e"
                class="mt-8 rounded-xl border border-dashed border-slate-700 p-8 text-center transition-all duration-300 hover:border-slate-600"
            >
                <p class="text-slate-400">
                    Cadastre uma conta para ver saldo projetado e lançamentos.
                </p>
                <RouterLink
                    to="/contas"
                    class="mt-4 inline-block rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-500 active:scale-[0.98]"
                >
                    Ir para contas
                </RouterLink>
            </div>

            <div
                v-else
                key="ok"
            >
                <DashboardQuickActions
                    :show-checkin-nudge="showCheckinNudge"
                    @open-checkin="openCheckin"
                    @open-onboarding="openOnboarding"
                />

                <div class="mt-4 rounded-xl border border-slate-800 bg-slate-900/30 p-4">
                    <div class="grid gap-3 md:grid-cols-4 md:items-end">
                        <div>
                            <label class="text-xs text-slate-500">Data inicial</label>
                            <DzDatePicker
                                v-model="projectionFromDate"
                                class="mt-1 block w-full"
                                input-class="w-full min-w-0"
                                :min-date="projectionLimits.minFrom"
                                :max-date="projectionLimits.maxFrom"
                                @update:model-value="onProjectionFromChange"
                            />
                        </div>

                        <div>
                            <label class="text-xs text-slate-500">Data final</label>
                            <DzDatePicker
                                v-model="projectionToDate"
                                class="mt-1 block w-full"
                                input-class="w-full min-w-0"
                                :min-date="projectionLimits.minTo"
                                :max-date="projectionLimits.maxTo"
                                @update:model-value="onProjectionToChange"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <p
                                v-if="projectionStart && projectionEnd"
                                class="text-[12px] text-slate-300"
                            >
                                Projetando de <span class="font-medium text-white">{{ formatDate(projectionStart) }}</span> até <span class="font-medium text-white">{{ formatDate(projectionEnd) }}</span>
                            </p>
                            <button
                                type="button"
                                class="dz-btn dz-btn-ghost mt-2 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="projectionRefreshing"
                                @click="handleRefreshProjection"
                            >
                                {{ projectionRefreshing ? 'Atualizando…' : 'Atualizar' }}
                            </button>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="rounded-md border border-slate-700 bg-slate-950/40 px-2 py-1 text-xs text-emerald-300">
                            Entradas: {{ formatBrl(summary.income_total) }}
                        </span>
                        <span class="rounded-md border border-slate-700 bg-slate-950/40 px-2 py-1 text-xs text-rose-300">
                            Saídas: {{ formatBrl(summary.expense_total) }}
                        </span>
                        <span
                            class="rounded-md border px-2 py-1 text-xs"
                            :class="Number(summary.net_cash_flow_total) >= 0 ? 'border-emerald-800/60 bg-emerald-950/20 text-emerald-300' : 'border-rose-800/60 bg-rose-950/20 text-rose-300'"
                        >
                            Sobra caixa: {{ formatBrl(summary.net_cash_flow_total) }}
                        </span>
                        <span class="rounded-md border border-slate-700 bg-slate-950/40 px-2 py-1 text-xs text-slate-400">
                            Aportes invest.: {{ formatBrl(summary.investment_contributions_cash_total ?? 0) }}
                        </span>
                        <span class="rounded-md border border-sky-900/50 bg-sky-950/20 px-2 py-1 text-xs text-sky-300">
                            Rend. invest. estim.: {{ formatBrl(summary.investment_estimated_yield_period ?? 0) }}
                        </span>
                        <span
                            class="rounded-md border px-2 py-1 text-xs"
                            :class="Number(summary.net_economic_including_investments ?? 0) >= 0 ? 'border-emerald-800/60 bg-emerald-950/30 text-emerald-200' : 'border-rose-800/60 bg-rose-950/30 text-rose-200'"
                        >
                            Sobra + rend. invest.: {{ formatBrl(summary.net_economic_including_investments ?? 0) }}
                        </span>
                    </div>
                </div>

                <DashboardSummaryCards
                    :accounts-count="accounts.length"
                    :debt-balance-total="debtTotals?.balance_total"
                    :summary="summary"
                />

                <DashboardProjectionPanel
                    :projection-days="projectionDays"
                    :summary="summary"
                />
            </div>
        </Transition>

        <OnboardingModal
            v-model:show="onboardingOpen"
            :user-id="auth.user?.id ?? 'anon'"
            :progress="onboardingProgress"
            @open-checkin="openCheckin"
            @navigate="onOnboardingNavigate"
        />

        <DailyCheckinModal
            v-model:show="checkinOpen"
            :accounts="accounts"
            :categories="categories"
            :user-id="auth.user?.id ?? 'anon'"
            :refresh-categories="refreshCategories"
            @saved="onCheckinSaved"
        />
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AppSkeleton from '../components/AppSkeleton.vue';
import DzDatePicker from '../components/DzDatePicker.vue';
import DashboardProjectionPanel from '../components/DashboardProjectionPanel.vue';
import DashboardQuickActions from '../components/DashboardQuickActions.vue';
import DashboardSummaryCards from '../components/DashboardSummaryCards.vue';
import DailyCheckinModal from '../components/DailyCheckinModal.vue';
import OnboardingModal from '../components/OnboardingModal.vue';
import { useDashboardData } from '../composables/useDashboardData';
import { useAuthStore } from '../stores/auth';
import { useToastStore } from '../stores/toast';
import { projectionDatePickerLimits } from '../utils/dateRange';

const auth = useAuthStore();
const toast = useToastStore();
const router = useRouter();
const userId = computed(() => auth.user?.id ?? 'anon');
const {
    loading,
    accounts,
    categories,
    debtTotals,
    summary,
    projectionDays,
    onboardingOpen,
    onboardingProgress,
    projectionFromDate,
    projectionToDate,
    projectionRefreshing,
    projectionStart,
    projectionEnd,
    checkinOpen,
    showCheckinNudge,
    openOnboarding,
    openCheckin,
    markCheckinSaved,
    refreshCategories,
    initDashboardData,
    refreshProjection,
    setProjectionFromDate,
    setProjectionToDate,
} = useDashboardData(userId);

const projectionLimits = computed(() =>
    projectionDatePickerLimits(projectionFromDate.value, projectionToDate.value),
);

async function onCheckinSaved() {
    await markCheckinSaved();
    toast.success('Check-in diário registrado.');
}

function onOnboardingNavigate(path) {
    if (!path) return;
    onboardingOpen.value = false;
    router.push(path);
}

function onProjectionFromChange() {
    setProjectionFromDate(projectionFromDate.value);
    refreshProjection();
}

function onProjectionToChange() {
    setProjectionToDate(projectionToDate.value);
    refreshProjection();
}

async function handleRefreshProjection() {
    try {
        await refreshProjection();
    } catch {
        toast.error('Não foi possível atualizar a previsão.');
    }
}

function formatDate(d) {
    if (!d) return '—';
    return new Date(`${d}T12:00:00`).toLocaleDateString('pt-BR');
}

function formatBrl(v) {
    if (v === null || v === undefined) return '—';
    return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

onMounted(async () => {
    await initDashboardData();
});
</script>
