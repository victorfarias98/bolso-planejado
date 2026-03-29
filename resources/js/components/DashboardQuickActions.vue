<template>
    <div>
        <div
            v-if="showCheckinNudge"
            class="mt-6 rounded-xl border border-emerald-900/50 bg-emerald-950/20 px-4 py-3 text-sm"
        >
            <p class="text-emerald-200">
                Check-in diário pendente: registre rapidamente entradas e saídas de hoje.
            </p>
            <button
                type="button"
                class="mt-2 text-xs font-medium text-emerald-400 hover:underline"
                @click="$emit('open-checkin')"
            >
                Fazer check-in agora →
            </button>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <button
                type="button"
                class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300 transition hover:bg-slate-800"
                @click="$emit('open-checkin')"
            >
                Check-in diário
            </button>
            <button
                type="button"
                class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300 transition hover:bg-slate-800"
                @click="$emit('open-onboarding')"
            >
                Ver onboarding
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    showCheckinNudge: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['open-checkin', 'open-onboarding']);
</script>
