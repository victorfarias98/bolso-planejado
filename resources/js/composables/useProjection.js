import { computed, ref } from 'vue';
import { http } from '../api/http';
import { isoAddDays, MAX_PROJECTION_RANGE_DAYS } from '../utils/dateRange';

export function useProjection() {
    function toFiniteNumber(v) {
        const n = Number(v);
        return Number.isFinite(n) ? n : 0;
    }

    const loading = ref(true);
    const projectionSortDir = ref('desc');
    const accounts = ref([]);
    const accountId = ref('');
    const horizon = ref(30);
    const payload = ref(null);
    const cashFloor = ref(loadCashFloor());
    const fromDate = ref(loadProjectionFromDate());
    const toDateAuto = ref(!localStorage.getItem('dz_projection_to_date'));
    const toDate = ref(loadProjectionToDate());

    const summary = computed(() => payload.value?.summary ?? {});
    const days = computed(() => payload.value?.days ?? []);
    const compactDays = computed(() => {
        const list = days.value;
        if (list.length <= 45) return list;
        const step = Math.ceil(list.length / 45);
        const sampled = [];
        for (let i = 0; i < list.length; i += step) sampled.push(list[i]);
        const last = list[list.length - 1];
        if (sampled[sampled.length - 1]?.date !== last?.date) sampled.push(last);
        return sampled;
    });
    const sortedTableDays = computed(() => {
        const list = [...days.value];
        const mult = projectionSortDir.value === 'asc' ? 1 : -1;
        list.sort((a, b) => a.date.localeCompare(b.date) * mult);
        return list;
    });
    const projectionStart = computed(() => payload.value?.projection_start ?? '');
    const projectionEnd = computed(() => payload.value?.projection_end ?? '');
    const projectedInvestmentsEnd = computed(() => summary.value?.projected_investments_end ?? null);
    const projectedNetWorthEnd = computed(() => summary.value?.projected_net_worth_end ?? null);

    const chartLabels = computed(() =>
        days.value.map((d) => {
            const dt = new Date(d.date + 'T12:00:00');
            return dt.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
        }),
    );

    const chartValues = computed(() => days.value.map((d) => Number(balanceForDayRaw(d))));
    const daysWithStatus = computed(() =>
        days.value.map((d) => {
            const balance = toFiniteNumber(balanceForDayRaw(d));
            const floor = toFiniteNumber(cashFloor.value);
            let status = 'ok';
            if (balance < floor) {
                status = 'risk';
            } else if (balance < floor * 1.3) {
                status = 'attention';
            }
            return { ...d, balance, status };
        }),
    );
    const nextCriticalDays = computed(() =>
        daysWithStatus.value
            .filter((d) => d.status !== 'ok')
            .slice(0, 7),
    );
    const belowFloorCount = computed(() =>
        daysWithStatus.value.filter((d) => d.balance < toFiniteNumber(cashFloor.value)).length,
    );

    const compactRange = computed(() => {
        if (!days.value.length) {
            return { min: 0, max: 1, span: 1 };
        }
        let min = Infinity;
        let max = -Infinity;
        for (const d of days.value) {
            const n = toFiniteNumber(balanceForDayRaw(d));
            if (n < min) min = n;
            if (n > max) max = n;
        }
        const span = Math.max(1, max - min);
        return { min, max, span };
    });

    function onProjectionTableSort(column) {
        if (column === 'date') {
            projectionSortDir.value = projectionSortDir.value === 'asc' ? 'desc' : 'asc';
        }
    }

    function balanceForDayRaw(d) {
        if (!accountId.value) return d.end_balance_consolidated;
        return d.end_balances_by_account?.[accountId.value] ?? '0';
    }

    function balanceForDay(d) {
        return balanceForDayRaw(d);
    }

    function setCashFloor(v) {
        const n = Math.max(0, toFiniteNumber(v));
        cashFloor.value = n;
        localStorage.setItem('dz_cash_floor', String(n));
    }

    function barHeight(d) {
        const n = toFiniteNumber(balanceForDayRaw(d));
        const { min, span } = compactRange.value;
        // Scale by variation range to improve readability.
        const normalized = ((n - min) / span) * 100;
        const pct = Number.isFinite(normalized) ? normalized : 0;
        return Math.max(28, Math.min(100, pct));
    }

    function barClass(d) {
        const n = toFiniteNumber(balanceForDayRaw(d));
        if (n < 0) return 'bg-rose-500';
        if (n < 100) return 'bg-amber-500';
        return 'bg-emerald-500';
    }

    async function carregarContas() {
        const { data } = await http.get('/financial-accounts');
        accounts.value = data.data ?? [];
    }

    async function carregar() {
        loading.value = true;
        try {
            const params = { horizon_days: horizon.value };
            if (fromDate.value) params.from = fromDate.value;
            if (toDate.value) params.to = toDate.value;
            if (accountId.value) params.financial_account_id = accountId.value;
            const { data } = await http.get('/projection', { params });
            const body = data?.data ?? data;
            if (!body || typeof body !== 'object') {
                throw new Error('Resposta de projeção inválida.');
            }
            payload.value = body;
        } finally {
            loading.value = false;
        }
    }

    async function init() {
        await carregarContas();
        await carregar();
    }

    function setFromDate(v) {
        // Keep as YYYY-MM-DD
        const raw = String(v ?? '');
        const safe = raw.match(/^\d{4}-\d{2}-\d{2}$/) ? raw : todayStr();
        fromDate.value = safe;
        localStorage.setItem('dz_projection_from_date', safe);

        // Se o usuário ainda não personalizou a data final, mantenha consistência com o horizonte.
        if (toDateAuto.value) {
            const next = isoAddDays(safe, Number(horizon.value) - 1);
            toDate.value = next;
            localStorage.setItem('dz_projection_to_date', next);
        }
    }

    function setToDate(v) {
        const raw = String(v ?? '');
        const safe = raw.match(/^\d{4}-\d{2}-\d{2}$/) ? raw : isoAddDays(fromDate.value, Number(horizon.value) - 1);
        toDateAuto.value = false;
        toDate.value = safe;
        localStorage.setItem('dz_projection_to_date', safe);
    }

    function setDateRange(fromValue, toValue) {
        const fromRaw = String(fromValue ?? '');
        const safeFrom = fromRaw.match(/^\d{4}-\d{2}-\d{2}$/) ? fromRaw : todayStr();

        const toRaw = String(toValue ?? '');
        const fallbackTo = isoAddDays(safeFrom, Number(horizon.value) - 1) ?? safeFrom;
        const safeTo = toRaw.match(/^\d{4}-\d{2}-\d{2}$/) ? toRaw : fallbackTo;

        fromDate.value = safeFrom;
        toDate.value = safeTo;
        toDateAuto.value = false;

        localStorage.setItem('dz_projection_from_date', safeFrom);
        localStorage.setItem('dz_projection_to_date', safeTo);
    }

    function loadCashFloor() {
        const raw = localStorage.getItem('dz_cash_floor');
        const n = Number(raw ?? '1000');
        return Number.isFinite(n) ? Math.max(0, n) : 1000;
    }

    function todayStr() {
        return new Date().toISOString().slice(0, 10);
    }

    function loadProjectionFromDate() {
        const raw = localStorage.getItem('dz_projection_from_date');
        return raw && raw.match(/^\d{4}-\d{2}-\d{2}$/) ? raw : todayStr();
    }

    function loadProjectionToDate() {
        const base = fromDate.value || todayStr();
        const fallback = isoAddDays(base, Number(horizon.value) - 1) ?? base;

        const raw = localStorage.getItem('dz_projection_to_date');
        if (!raw || !raw.match(/^\d{4}-\d{2}-\d{2}$/)) {
            return fallback;
        }

        const from = fromDate.value || loadProjectionFromDate();
        const fromDt = new Date(`${from}T12:00:00`);
        const toDt = new Date(`${raw}T12:00:00`);
        if (Number.isNaN(fromDt.getTime()) || Number.isNaN(toDt.getTime())) {
            return fallback;
        }

        const diffDays = Math.round((toDt.getTime() - fromDt.getTime()) / (24 * 60 * 60 * 1000));
        if (diffDays < 0 || diffDays > MAX_PROJECTION_RANGE_DAYS) {
            return fallback;
        }

        return raw;
    }

    return {
        loading,
        projectionSortDir,
        accounts,
        accountId,
        horizon,
        summary,
        days,
        compactDays,
        sortedTableDays,
        projectionStart,
        projectionEnd,
        projectedInvestmentsEnd,
        projectedNetWorthEnd,
        cashFloor,
        fromDate,
        toDate,
        daysWithStatus,
        nextCriticalDays,
        belowFloorCount,
        chartLabels,
        chartValues,
        onProjectionTableSort,
        balanceForDay,
        barHeight,
        barClass,
        setCashFloor,
        setFromDate,
        setToDate,
        setDateRange,
        carregar,
        init,
    };
}
