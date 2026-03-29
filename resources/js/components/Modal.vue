<template>
    <Teleport to="body">
        <Transition enter-active-class="transition duration-220 ease-out" leave-active-class="transition duration-160 ease-in" enter-from-class="opacity-0" leave-to-class="opacity-0">
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex min-h-full items-end justify-center p-0 sm:items-center sm:p-4"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="headingId"
            >
                <div
                    class="absolute inset-0 bg-slate-950/85 backdrop-blur-[2px]"
                    @click="close"
                />
                <div
                    ref="panelRef"
                    class="dz-modal-panel relative z-10 flex max-h-[min(92vh,800px)] w-full max-w-2xl flex-col overflow-hidden rounded-t-2xl border border-slate-800 bg-slate-900 shadow-2xl sm:max-h-[min(90vh,800px)] sm:rounded-2xl"
                    tabindex="-1"
                    @click.stop
                    @keydown.escape.prevent="close"
                >
                    <div class="mx-auto mt-2 h-1.5 w-12 rounded-full bg-slate-700 sm:hidden" />
                    <div
                        v-if="title || $slots.title"
                        class="flex flex-shrink-0 items-center justify-between gap-3 border-b border-slate-800 px-4 py-3"
                    >
                        <h2
                            :id="headingId"
                            class="text-lg font-semibold text-white"
                        >
                            <slot name="title">{{ title }}</slot>
                        </h2>
                        <button
                            type="button"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-800 hover:text-white"
                            aria-label="Fechar"
                            @click="close"
                        >
                            <span class="text-xl leading-none">&times;</span>
                        </button>
                    </div>
                    <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4">
                        <slot />
                    </div>
                    <div
                        v-if="$slots.footer"
                        class="flex-shrink-0 border-t border-slate-800 px-4 py-3"
                    >
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { nextTick, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:show', 'close']);

const headingId = `dz-modal-h-${Math.random().toString(36).slice(2, 11)}`;
const panelRef = ref(null);

function close() {
    emit('update:show', false);
    emit('close');
}

watch(
    () => props.show,
    async (v) => {
        document.body.classList.toggle('overflow-hidden', !!v);
        if (v) {
            await nextTick();
            panelRef.value?.focus();
        }
    },
    { immediate: true },
);

onUnmounted(() => document.body.classList.remove('overflow-hidden'));
</script>
