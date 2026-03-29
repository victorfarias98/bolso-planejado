<template>
    <div>
        <div
            v-if="chartLabels.length"
            class="mt-8 rounded-xl border border-slate-800 bg-slate-900/30 p-4 transition-shadow duration-300 hover:shadow-lg hover:shadow-emerald-950/10"
        >
            <h2 class="mb-1 text-sm font-medium text-slate-400">
                Período selecionado (saldo consolidado)
            </h2>
            <ProjectionLineChart
                compact
                :labels="chartLabels"
                :values="chartValues"
                :detail-days="projectionDays"
                title="Saldo"
                @day-click="abrirDia"
            />
        </div>

        <p
            v-if="summary?.disclaimer"
            class="mt-6 text-xs text-slate-500"
        >
            {{ summary.disclaimer }}
        </p>

        <div class="mt-8">
            <RouterLink
                to="/previsao"
                class="text-sm font-medium text-emerald-400 transition hover:text-emerald-300 hover:underline"
            >
                Ver calendário completo de previsão →
            </RouterLink>
        </div>

        <Modal
            v-model:show="detailOpen"
            title="Detalhes do dia"
        >
            <template v-if="selectedDay">
                <p class="text-sm text-slate-400">
                    {{ formatDate(selectedDay.date) }}
                </p>
                <p class="mt-2 text-lg font-semibold" :class="Number(selectedDay.end_balance_consolidated) < 0 ? 'text-rose-300' : 'text-emerald-300'">
                    Saldo no fim do dia: {{ formatBrl(selectedDay.end_balance_consolidated) }}
                </p>

                <div class="mt-4 rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                    <p class="text-xs uppercase text-slate-500">
                        Entradas
                    </p>
                    <div class="mt-2 space-y-1 text-sm text-slate-300">
                        <p v-if="!incomeMoves.length">—</p>
                        <p v-for="(m, idx) in incomeMoves" :key="`in-${idx}`">+ {{ m.description ?? 'Entrada' }} ({{ formatBrl(m.signed_amount) }})</p>
                    </div>
                </div>

                <div class="mt-3 rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                    <p class="text-xs uppercase text-slate-500">
                        Débitos
                    </p>
                    <div class="mt-2 space-y-1 text-sm text-slate-300">
                        <p v-if="!expenseMoves.length">—</p>
                        <p v-for="(m, idx) in expenseMoves" :key="`out-${idx}`">- {{ m.description ?? 'Saída' }} ({{ formatBrl(m.signed_amount) }})</p>
                    </div>
                </div>
            </template>
        </Modal>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import Modal from './Modal.vue';
import ProjectionLineChart from './ProjectionLineChart.vue';

const props = defineProps({
    projectionDays: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        required: true,
    },
});

const chartLabels = computed(() =>
    props.projectionDays.map((d) => {
        const dt = new Date(d.date + 'T12:00:00');
        return dt.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
    }),
);

const chartValues = computed(() =>
    props.projectionDays.map((d) => Number(d.end_balance_consolidated)),
);

const detailOpen = ref(false);
const selectedDay = ref(null);

const incomeMoves = computed(() =>
    (selectedDay.value?.movements ?? []).filter((m) => Number(m.signed_amount) > 0),
);
const expenseMoves = computed(() =>
    (selectedDay.value?.movements ?? []).filter((m) => Number(m.signed_amount) < 0),
);

function abrirDia(day) {
    selectedDay.value = day;
    detailOpen.value = true;
}

function formatBrl(v) {
    if (v === null || v === undefined) return '—';
    return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function formatDate(d) {
    if (!d) return '—';
    return new Date(`${d}T12:00:00`).toLocaleDateString('pt-BR');
}
</script>
