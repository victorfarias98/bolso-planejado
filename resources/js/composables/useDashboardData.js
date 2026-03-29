import { computed, ref, unref } from 'vue';
import { http } from '../api/http';
import { isoAddDays, MAX_PROJECTION_RANGE_DAYS } from '../utils/dateRange';

export function useDashboardData(userIdRef) {
    const loading = ref(true);
    const accounts = ref([]);
    const categories = ref([]);
    const debtTotals = ref(null);
    const summary = ref({
        minimum_balance: null,
        minimum_balance_date: null,
        first_negative_date: null,
        disclaimer: '',
    });
    const projectionDays = ref([]);
    const projectionStart = ref('');
    const projectionEnd = ref('');
    const projectionFromDate = ref(loadProjectionFromDate());
    const projectionToAuto = ref(!localStorage.getItem('dz_projection_to_date'));
    const projectionToDate = ref(loadProjectionToDate());
    const projectionRefreshing = ref(false);
    const onboardingOpen = ref(false);
    const checkinOpen = ref(false);
    const checkinDoneToday = ref(false);
    const onboardingProgress = ref({
        hasAccount: false,
        hasRecurrence: false,
        hasDebt: false,
        hasTransactionThisMonth: false,
        checkedInToday: false,
    });

    const showCheckinNudge = computed(() => !checkinDoneToday.value && accounts.value.length > 0);

    function todayStr() {
        return new Date().toISOString().slice(0, 10);
    }

    function loadProjectionFromDate() {
        const raw = localStorage.getItem('dz_projection_from_date');
        return raw && raw.match(/^\d{4}-\d{2}-\d{2}$/) ? raw : todayStr();
    }

    function setProjectionFromDate(v) {
        const raw = String(v ?? '');
        const safe = raw.match(/^\d{4}-\d{2}-\d{2}$/) ? raw : todayStr();
        projectionFromDate.value = safe;
        localStorage.setItem('dz_projection_from_date', safe);

        if (projectionToAuto.value) {
            const next = isoAddDays(safe, 29);
            projectionToDate.value = next;
            localStorage.setItem('dz_projection_to_date', next);
        }
    }

    function loadProjectionToDate() {
        const raw = localStorage.getItem('dz_projection_to_date');
        const from = projectionFromDate.value;
        const fromValid = from && from.match(/^\d{4}-\d{2}-\d{2}$/);
        const fallback = fromValid ? isoAddDays(from, 29) : todayStr();

        if (!raw || !raw.match(/^\d{4}-\d{2}-\d{2}$/)) {
            return fallback;
        }

        const to = raw;
        const fromDt = new Date(`${from}T12:00:00`);
        const toDt = new Date(`${to}T12:00:00`);
        if (Number.isNaN(fromDt.getTime()) || Number.isNaN(toDt.getTime())) {
            return fallback;
        }

        const diffDays = Math.round((toDt.getTime() - fromDt.getTime()) / (24 * 60 * 60 * 1000));
        if (diffDays < 0 || diffDays > MAX_PROJECTION_RANGE_DAYS) {
            return fallback;
        }

        return to;
    }

    function setProjectionToDate(v) {
        const raw = String(v ?? '');
        const safe = raw.match(/^\d{4}-\d{2}-\d{2}$/) ? raw : isoAddDays(projectionFromDate.value, 29);
        projectionToAuto.value = false;
        projectionToDate.value = safe;
        localStorage.setItem('dz_projection_to_date', safe);
    }

    function monthRange() {
        const now = new Date();
        const start = new Date(now.getFullYear(), now.getMonth(), 1);
        const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        const toIso = (d) => d.toISOString().slice(0, 10);
        return { from: toIso(start), to: toIso(end) };
    }

    function userId() {
        return unref(userIdRef) ?? 'anon';
    }

    function onboardingKey() {
        return `dz_onboarding_done_${userId()}`;
    }

    function checkinKey() {
        return `dz_checkin_date_${userId()}`;
    }

    function openOnboarding() {
        onboardingOpen.value = true;
    }

    function openCheckin() {
        checkinOpen.value = true;
    }

    function applyProjectionResponse(proj) {
        const body = proj?.data?.data ?? proj?.data;
        if (!body || typeof body !== 'object') {
            throw new Error('Resposta de projeção inválida.');
        }
        summary.value = body.summary ?? summary.value;
        projectionDays.value = body.days ?? [];
        projectionStart.value = body.projection_start ?? '';
        projectionEnd.value = body.projection_end ?? '';
    }

    async function refreshProjection() {
        projectionRefreshing.value = true;
        try {
            const proj = await http.get('/projection', {
                params: {
                    horizon_days: 30,
                    from: projectionFromDate.value,
                    to: projectionToDate.value,
                },
            });
            applyProjectionResponse(proj);
        } finally {
            projectionRefreshing.value = false;
        }
    }

    async function markCheckinSaved() {
        localStorage.setItem(checkinKey(), todayStr());
        checkinDoneToday.value = true;
        await refreshProjection();
    }

    async function refreshCategories() {
        try {
            const { data } = await http.get('/categories');
            categories.value = data.data ?? [];
        } catch {
            /* mantém lista atual se a API falhar */
        }
    }

    async function initDashboardData() {
        loading.value = true;
        try {
            const { from, to } = monthRange();
            const projectionParams = {
                horizon_days: 30,
                from: projectionFromDate.value,
                to: projectionToDate.value,
            };

            const [acc, cat, debtsRes, recRes, txRes, projRes] = await Promise.all([
                http.get('/financial-accounts'),
                http.get('/categories'),
                http.get('/debts').catch(() => ({ data: { meta: { totals: null } } })),
                http.get('/recurrence-series').catch(() => ({ data: { data: [] } })),
                http.get('/transactions', { params: { per_page: 1, from, to } }).catch(() => ({ data: { meta: { total: 0 } } })),
                http.get('/projection', { params: projectionParams }).catch(() => null),
            ]);
            accounts.value = acc.data.data ?? [];
            categories.value = cat.data.data ?? [];
            debtTotals.value = debtsRes.data.meta?.totals ?? null;
            checkinDoneToday.value = localStorage.getItem(checkinKey()) === todayStr();
            onboardingProgress.value = {
                hasAccount: (acc.data.data ?? []).length > 0,
                hasRecurrence: (recRes.data.data ?? []).some((r) => r.is_active),
                hasDebt: Number(debtsRes.data.meta?.totals?.active_count ?? 0) > 0,
                hasTransactionThisMonth: Number(txRes.data.meta?.total ?? 0) > 0,
                checkedInToday: checkinDoneToday.value,
            };
            const seenOnboarding = localStorage.getItem(onboardingKey()) === '1';
            if (!seenOnboarding) {
                onboardingOpen.value = true;
            }
            if (projRes) {
                try {
                    applyProjectionResponse(projRes);
                } catch {
                    /* mantém resumo vazio se a resposta vier inválida */
                }
            }
        } finally {
            loading.value = false;
        }
    }

    return {
        loading,
        accounts,
        categories,
        debtTotals,
        summary,
        projectionDays,
        projectionStart,
        projectionEnd,
        projectionFromDate,
        projectionToDate,
        projectionRefreshing,
        onboardingOpen,
        checkinOpen,
        checkinDoneToday,
        onboardingProgress,
        showCheckinNudge,
        openOnboarding,
        openCheckin,
        refreshProjection,
        setProjectionFromDate,
        setProjectionToDate,
        markCheckinSaved,
        refreshCategories,
        initDashboardData,
    };
}
