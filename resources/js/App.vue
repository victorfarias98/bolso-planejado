<template>
    <Transition name="expand">
        <div
            v-if="routeLoading"
            class="fixed left-0 right-0 top-0 z-[70] h-1 bg-emerald-500/90 dz-loading-pulse"
            aria-hidden="true"
        />
    </Transition>
    <router-view v-slot="{ Component }">
        <Transition
            name="page"
            mode="out-in"
        >
            <component :is="Component" />
        </Transition>
    </router-view>
</template>

<script setup>
import { onUnmounted, ref } from 'vue';
import router from './router';

const routeLoading = ref(false);
let loadingTimer = null;

const removeBefore = router.beforeEach((_to, _from, next) => {
    routeLoading.value = true;
    next();
});

const removeAfter = router.afterEach(() => {
    if (loadingTimer) clearTimeout(loadingTimer);
    loadingTimer = setTimeout(() => {
        routeLoading.value = false;
    }, 120);
});

const removeError = router.onError(() => {
    routeLoading.value = false;
});

onUnmounted(() => {
    if (loadingTimer) clearTimeout(loadingTimer);
    removeBefore();
    removeAfter();
    removeError();
});
</script>
