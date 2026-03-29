<template>
    <div class="mt-4 flex items-center justify-between rounded-lg border border-slate-800 bg-slate-900/60 px-4 py-2 text-sm">
        <p class="text-slate-300">
            {{ countLabel }}
        </p>
        <button
            type="button"
            class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-rose-500"
            @click="$emit('action')"
        >
            {{ actionLabel }}
        </button>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    selectedCount: {
        type: Number,
        required: true,
    },
    singularLabel: {
        type: String,
        default: 'selecionado',
    },
    pluralLabel: {
        type: String,
        default: 'selecionados',
    },
    actionLabel: {
        type: String,
        default: 'Excluir selecionados',
    },
});

defineEmits(['action']);

const countLabel = computed(() =>
    `${props.selectedCount} ${props.selectedCount === 1 ? props.singularLabel : props.pluralLabel}`,
);
</script>
