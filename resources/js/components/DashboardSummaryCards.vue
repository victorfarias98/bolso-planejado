<template>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div
            class="rounded-xl border border-slate-800 bg-slate-900/40 p-5 transition duration-300 hover:-translate-y-1 hover:border-slate-700 hover:shadow-lg hover:shadow-emerald-950/20"
        >
            <p class="text-xs font-medium uppercase text-slate-500">
                Contas
            </p>
            <p class="mt-2 text-2xl font-semibold text-white">
                {{ accountsCount }}
            </p>
        </div>
        <div
            class="rounded-xl border border-slate-800 bg-slate-900/40 p-5 transition duration-300 hover:-translate-y-1 hover:border-slate-700 hover:shadow-lg hover:shadow-emerald-950/20"
        >
            <p class="text-xs font-medium uppercase text-slate-500">
                Dívidas (saldo ativo)
            </p>
            <p class="mt-2 text-2xl font-semibold text-rose-300">
                {{ formatBrl(debtBalanceTotal) }}
            </p>
            <RouterLink
                to="/dividas"
                class="mt-2 inline-block text-xs text-emerald-400 hover:underline"
            >
                Ver dívidas →
            </RouterLink>
        </div>
        <div
            class="rounded-xl border border-slate-800 bg-slate-900/40 p-5 transition duration-300 hover:-translate-y-1 hover:border-slate-700 hover:shadow-lg hover:shadow-emerald-950/20"
        >
            <p class="text-xs font-medium uppercase text-slate-500">
                Saldo atual
            </p>
            <p
                class="mt-2 text-2xl font-semibold"
                :class="currentBalanceClass"
            >
                {{ formatBrl(summary.current_balance) }}
            </p>
            <p
                v-if="summary.current_balance_date"
                class="mt-1 text-xs text-slate-500"
            >
                em {{ formatDate(summary.current_balance_date) }}
            </p>
        </div>
        <div
            class="rounded-xl border border-slate-800 bg-slate-900/40 p-5 transition duration-300 hover:-translate-y-1 hover:border-slate-700 hover:shadow-lg hover:shadow-emerald-950/20"
        >
            <p class="text-xs font-medium uppercase text-slate-500">
                Menor saldo (30 dias)
            </p>
            <p
                class="mt-2 text-2xl font-semibold"
                :class="minBalanceClass"
            >
                {{ formatBrl(summary.minimum_balance) }}
            </p>
            <p
                v-if="summary.minimum_balance_date"
                class="mt-1 text-xs text-slate-500"
            >
                em {{ formatDate(summary.minimum_balance_date) }}
            </p>
        </div>
        <div
            class="rounded-xl border border-slate-800 bg-slate-900/40 p-5 transition duration-300 hover:-translate-y-1 hover:border-slate-700 hover:shadow-lg hover:shadow-emerald-950/20"
        >
            <p class="text-xs font-medium uppercase text-slate-500">
                Primeiro dia no vermelho
            </p>
            <p
                class="mt-2 text-lg font-medium"
                :class="summary.first_negative_date ? 'text-rose-400' : 'text-emerald-400'"
            >
                {{ summary.first_negative_date ? formatDate(summary.first_negative_date) : 'Nenhum nos próximos 30 dias' }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    accountsCount: {
        type: Number,
        required: true,
    },
    debtBalanceTotal: {
        type: [String, Number, null],
        default: null,
    },
    summary: {
        type: Object,
        required: true,
    },
});

const minBalanceClass = computed(() => {
    const v = props.summary?.minimum_balance;
    if (v === null || v === undefined) {
        return 'text-slate-300';
    }
    return Number(v) < 0 ? 'text-rose-400' : 'text-white';
});

const currentBalanceClass = computed(() => {
    const v = props.summary?.current_balance;
    if (v === null || v === undefined) {
        return 'text-slate-300';
    }
    return Number(v) < 0 ? 'text-rose-400' : 'text-emerald-300';
});

function formatBrl(v) {
    if (v === null || v === undefined) {
        return '—';
    }
    return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function formatDate(d) {
    if (!d) {
        return '';
    }
    return new Date(d + 'T12:00:00').toLocaleDateString('pt-BR');
}
</script>
