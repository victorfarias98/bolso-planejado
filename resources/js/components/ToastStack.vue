<template>
    <div
        class="pointer-events-none fixed bottom-4 right-4 z-[100] flex w-[min(100vw-2rem,22rem)] flex-col gap-2 sm:bottom-6 sm:right-6"
        aria-live="polite"
    >
        <TransitionGroup name="toast">
            <div
                v-for="t in toast.items"
                :key="t.id"
                class="pointer-events-auto relative rounded-xl border px-4 py-3 pr-10 text-sm shadow-lg backdrop-blur-sm"
                :class="variantClass(t.variant)"
            >
                {{ t.message }}
                <button
                    type="button"
                    class="absolute right-2 top-2 rounded p-1 text-lg leading-none opacity-60 hover:opacity-100"
                    aria-label="Fechar"
                    @click="toast.dismiss(t.id)"
                >
                    ×
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup>
import { useToastStore } from '../stores/toast';

const toast = useToastStore();

function variantClass(v) {
    if (v === 'error') {
        return 'border-rose-500/40 bg-rose-950/95 text-rose-100';
    }
    if (v === 'info') {
        return 'border-sky-500/40 bg-sky-950/95 text-sky-100';
    }
    return 'border-emerald-500/40 bg-emerald-950/95 text-emerald-100';
}
</script>
