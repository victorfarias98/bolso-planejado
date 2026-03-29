<template>
    <th
        scope="col"
        :class="[
            baseClass,
            sortable ? 'cursor-pointer select-none hover:text-slate-300' : '',
        ]"
        @click="sortable && $emit('sort', column)"
    >
        <span class="inline-flex items-center gap-1">
            <slot />
            <span
                v-if="sortable && modelKey === column"
                class="font-normal text-emerald-400"
                aria-hidden="true"
            >{{ modelDir === 'asc' ? '↑' : '↓' }}</span>
        </span>
    </th>
</template>

<script setup>
defineProps({
    column: {
        type: String,
        required: true,
    },
    modelKey: {
        type: String,
        required: true,
    },
    modelDir: {
        type: String,
        required: true,
    },
    sortable: {
        type: Boolean,
        default: true,
    },
    baseClass: {
        type: String,
        default: 'px-4 py-3',
    },
});

defineEmits(['sort']);
</script>
