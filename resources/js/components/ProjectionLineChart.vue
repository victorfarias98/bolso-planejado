<template>
    <div
        class="relative w-full"
        :class="compact ? 'h-48' : 'h-72'"
    >
        <canvas ref="canvasRef" />
    </div>
</template>

<script setup>
import {
    CategoryScale,
    Chart,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

Chart.register(
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Filler,
    Tooltip,
    Legend,
);

const props = defineProps({
    labels: {
        type: Array,
        required: true,
    },
    values: {
        type: Array,
        required: true,
    },
    detailDays: {
        type: Array,
        default: () => [],
    },
    title: {
        type: String,
        default: 'Saldo projetado',
    },
    compact: {
        type: Boolean,
        default: false,
    },
});
const emit = defineEmits(['day-click']);

const canvasRef = ref(null);
let chart = null;

function formatBrl(v) {
    return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            label: props.title,
            data: props.values.map((v) => Number(v)),
            borderColor: '#34d399',
            backgroundColor: (context) => {
                const ctx = context.chart.ctx;
                const h = context.chart.height || 200;
                const g = ctx.createLinearGradient(0, 0, 0, h);
                g.addColorStop(0, 'rgba(52, 211, 153, 0.35)');
                g.addColorStop(1, 'rgba(52, 211, 153, 0.02)');

                return g;
            },
            borderWidth: 2,
            fill: true,
            tension: 0.35,
            pointRadius: props.compact || props.values.length > 45 ? 0 : 3,
            pointHoverRadius: 5,
            pointBackgroundColor: '#6ee7b7',
            segment: {
                borderColor: (ctx) => {
                    const a = Number(ctx.p0?.parsed?.y);
                    const b = Number(ctx.p1?.parsed?.y);
                    if (Number.isNaN(a) || Number.isNaN(b)) {
                        return '#34d399';
                    }

                    return a < 0 || b < 0 ? '#fb7185' : '#34d399';
                },
            },
        },
    ],
}));

function build() {
    if (! canvasRef.value || ! props.labels.length) {
        return;
    }

    chart?.destroy();
    chart = new Chart(canvasRef.value, {
        type: 'line',
        data: chartData.value,
        options: {
            onClick: (_event, elements) => {
                if (!elements?.length) return;
                const idx = elements[0].index;
                const day = props.detailDays?.[idx];
                if (day) emit('day-click', day);
            },
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    display: ! props.compact,
                    labels: { color: '#94a3b8', font: { size: 11 } },
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: '#e2e8f0',
                    bodyColor: '#cbd5e1',
                    borderColor: 'rgba(51, 65, 85, 0.8)',
                    borderWidth: 1,
                    callbacks: {
                        title(items) {
                            const idx = items?.[0]?.dataIndex ?? -1;
                            const day = props.detailDays?.[idx];
                            if (!day?.date) return items?.[0]?.label ?? '';
                            return new Date(`${day.date}T12:00:00`).toLocaleDateString('pt-BR');
                        },
                        label(ctx) {
                            const v = ctx.parsed.y;
                            return `Saldo: ${formatBrl(v)}`;
                        },
                        afterBody(items) {
                            const idx = items?.[0]?.dataIndex ?? -1;
                            const day = props.detailDays?.[idx];
                            const moves = day?.movements ?? [];
                            if (!moves.length) {
                                return ['Movimentos: —'];
                            }
                            const incomes = moves.filter((m) => Number(m.signed_amount) > 0);
                            const expenses = moves.filter((m) => Number(m.signed_amount) < 0);
                            const lines = [];
                            if (incomes.length) {
                                lines.push('Entradas:');
                                for (const m of incomes.slice(0, 3)) {
                                    lines.push(`+ ${m.description ?? 'Entrada'} (${formatBrl(m.signed_amount)})`);
                                }
                                if (incomes.length > 3) lines.push(`+ ... (${incomes.length - 3} mais)`);
                            }
                            if (expenses.length) {
                                lines.push('Débitos:');
                                for (const m of expenses.slice(0, 3)) {
                                    lines.push(`- ${m.description ?? 'Saída'} (${formatBrl(m.signed_amount)})`);
                                }
                                if (expenses.length > 3) lines.push(`- ... (${expenses.length - 3} mais)`);
                            }
                            return lines;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: 'rgba(51, 65, 85, 0.35)' },
                    ticks: {
                        color: '#64748b',
                        maxRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 12,
                    },
                },
                y: {
                    grid: { color: 'rgba(51, 65, 85, 0.35)' },
                    ticks: {
                        color: '#64748b',
                        callback: (val) =>
                            Number(val).toLocaleString('pt-BR', {
                                style: 'currency',
                                currency: 'BRL',
                                maximumFractionDigits: 0,
                            }),
                    },
                },
            },
            animation: {
                duration: 600,
                easing: 'easeOutQuart',
            },
        },
    });
}

onMounted(() => {
    build();
});

watch(
    () => [props.labels, props.values],
    () => build(),
    { deep: true },
);

onBeforeUnmount(() => {
    chart?.destroy();
    chart = null;
});
</script>
