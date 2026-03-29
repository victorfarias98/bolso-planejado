<template>
    <Modal
        :show="show"
        title="Bem-vindo ao Bolso Planejado"
        @update:show="emit('update:show', $event)"
        @close="emit('close')"
    >
        <div class="space-y-3 text-sm">
            <div class="flex items-center justify-between gap-3">
                <p class="font-medium text-white">
                    {{ steps[currentStep].title }}
                </p>
                <span
                    class="rounded-full px-2 py-0.5 text-xs"
                    :class="steps[currentStep].done ? 'bg-emerald-900/50 text-emerald-300' : 'bg-amber-900/40 text-amber-300'"
                >
                    {{ steps[currentStep].done ? 'Concluído' : 'Pendente' }}
                </span>
            </div>
            <p class="text-slate-400">
                {{ steps[currentStep].body }}
            </p>
            <div class="rounded-lg border border-slate-800 bg-slate-950/70 px-3 py-2 text-xs text-slate-500">
                Passo {{ currentStep + 1 }} de {{ steps.length }} · {{ doneCount }}/{{ steps.length }} concluídos
            </div>
            <div class="rounded-lg border border-slate-800 bg-slate-950/40 p-3">
                <p class="text-xs uppercase text-slate-500">Checklist</p>
                <ul class="mt-2 space-y-1 text-xs">
                    <li
                        v-for="s in steps"
                        :key="s.id"
                        class="flex items-center gap-2"
                    >
                        <span :class="s.done ? 'text-emerald-400' : 'text-slate-500'">
                            {{ s.done ? '✓' : '•' }}
                        </span>
                        <span :class="s.done ? 'text-slate-300' : 'text-slate-400'">{{ s.short }}</span>
                    </li>
                </ul>
            </div>
        </div>
        <template #footer>
            <div class="space-y-2">
                <button
                    v-if="!steps[currentStep].done"
                    type="button"
                    class="w-full rounded-lg border border-emerald-700/60 px-3 py-2 text-sm text-emerald-300 hover:bg-emerald-950/30"
                    @click="runPrimaryAction"
                >
                    {{ steps[currentStep].actionLabel }}
                </button>
                <div class="flex justify-between gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300 hover:bg-slate-800"
                        @click="skip"
                    >
                        Concluir depois
                    </button>
                    <div class="flex gap-2">
                    <button
                        v-if="currentStep > 0"
                        type="button"
                        class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300 hover:bg-slate-800"
                        @click="currentStep--"
                    >
                        Voltar
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-500"
                        @click="next"
                    >
                        {{ currentStep === steps.length - 1 ? 'Concluir' : 'Próximo' }}
                    </button>
                    </div>
                </div>
            </div>
        </template>
    </Modal>
</template>

<script setup>
import { computed, watch, ref } from 'vue';
import Modal from './Modal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    userId: {
        type: String,
        default: 'anon',
    },
    progress: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['update:show', 'finished', 'close', 'navigate', 'open-checkin']);

const currentStep = ref(0);
const steps = computed(() => [
    {
        id: 'accounts',
        short: 'Cadastrar pelo menos 1 conta',
        title: '1) Estruture suas contas',
        body: 'Cadastre conta corrente, carteira e outras contas que impactam seu caixa.',
        done: !!props.progress?.hasAccount,
        actionLabel: 'Ir para Contas',
        action: () => emit('navigate', '/contas'),
    },
    {
        id: 'recurrence',
        short: 'Criar recorrências de entrada e saída',
        title: '2) Ative suas recorrências',
        body: 'Defina salário, aluguel, parcelas e demais recorrências para previsibilidade.',
        done: !!props.progress?.hasRecurrence,
        actionLabel: 'Ir para Recorrências',
        action: () => emit('navigate', '/recorrencias'),
    },
    {
        id: 'debts',
        short: 'Cadastrar dívidas/acordos ativos',
        title: '3) Registre suas dívidas',
        body: 'Com dívidas cadastradas, o sistema prevê pressão de caixa e risco de atraso.',
        done: !!props.progress?.hasDebt,
        actionLabel: 'Ir para Dívidas',
        action: () => emit('navigate', '/dividas'),
    },
    {
        id: 'checkin',
        short: 'Fazer check-in diário',
        title: '4) Mantenha o check-in diário',
        body: '1 lançamento por dia melhora muito a qualidade da previsão e das recomendações.',
        done: !!props.progress?.checkedInToday,
        actionLabel: 'Abrir Check-in diário',
        action: () => emit('open-checkin'),
    },
    {
        id: 'report',
        short: 'Gerar relatório mensal em PDF',
        title: '5) Feche o mês com relatório',
        body: 'Em Transações você consegue baixar o relatório mensal em PDF para análise e acompanhamento.',
        done: !!props.progress?.hasTransactionThisMonth,
        actionLabel: 'Ir para Transações',
        action: () => emit('navigate', '/transacoes'),
    },
]);
const doneCount = computed(() => steps.value.filter((s) => s.done).length);

function storageKey() {
    return `dz_onboarding_done_${props.userId || 'anon'}`;
}

watch(
    () => props.show,
    (isOpen) => {
        if (isOpen) {
            currentStep.value = 0;
        }
    },
);

function finish() {
    localStorage.setItem(storageKey(), '1');
    emit('finished');
    emit('update:show', false);
}

function skip() {
    finish();
}

function next() {
    if (currentStep.value < steps.value.length - 1) {
        currentStep.value += 1;
        return;
    }
    finish();
}

function runPrimaryAction() {
    steps.value[currentStep.value]?.action?.();
}
</script>
