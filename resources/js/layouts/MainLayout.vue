<template>
    <div class="flex min-h-screen">
        <aside class="hidden w-56 shrink-0 flex-col border-r border-slate-800 bg-slate-900/80 p-4 md:flex">
            <div class="mb-8 px-2">
                <p class="text-xs font-medium uppercase tracking-wider text-emerald-400">Bolso Planejado</p>
                <p class="mt-1 truncate text-sm text-slate-400">{{ auth.user?.name }}</p>
            </div>
            <nav class="flex flex-1 flex-col gap-1">
                <RouterLink
                    v-for="l in links"
                    :key="l.to"
                    :to="l.to"
                    class="rounded-lg px-3 py-2 text-sm transition hover:bg-slate-800"
                    active-class="bg-slate-800 text-emerald-400"
                >
                    {{ l.label }}
                </RouterLink>
            </nav>
            <button
                type="button"
                class="mt-4 rounded-lg border border-slate-700 px-3 py-2 text-left text-sm text-slate-400 hover:bg-slate-800"
                @click="sair"
            >
                Sair
            </button>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-800 bg-slate-950/90 px-4 py-3 backdrop-blur md:hidden">
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-emerald-400">{{ currentLabel }}</p>
                    <p class="truncate text-xs text-slate-500">{{ auth.user?.name }}</p>
                </div>
                <button
                    type="button"
                    class="rounded-lg border border-slate-700 px-3 py-2 text-xs text-slate-300 active:scale-[0.98]"
                    @click="mobileOpen = !mobileOpen"
                >
                    {{ mobileOpen ? 'Fechar' : 'Menu' }}
                </button>
            </header>

            <Transition name="expand">
                <div
                    v-if="mobileOpen"
                    class="border-b border-slate-800 bg-slate-900 px-3 py-3 md:hidden"
                >
                    <div class="grid grid-cols-2 gap-2">
                        <RouterLink
                            v-for="l in links"
                            :key="l.to"
                            :to="l.to"
                            class="rounded-lg border border-slate-800 bg-slate-950/60 px-3 py-2 text-sm text-slate-200"
                            @click="mobileOpen = false"
                        >
                            <span class="mr-1 inline-flex h-4 w-4 align-[-2px]">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" aria-hidden="true">
                                    <path :d="iconPath(l.icon)" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            {{ l.label }}
                        </RouterLink>
                    </div>
                    <button
                        type="button"
                        class="mt-3 w-full rounded-lg border border-slate-700 px-3 py-2 text-left text-sm text-slate-300"
                        @click="sair"
                    >
                        Sair
                    </button>
                </div>
            </Transition>

            <main class="flex-1 p-4 pb-24 md:p-8 md:pb-8">
                <router-view v-slot="{ Component }">
                    <Transition name="page" mode="out-in">
                        <component :is="Component" />
                    </Transition>
                </router-view>
            </main>
        </div>

        <nav class="dz-bottom-nav md:hidden">
            <RouterLink
                v-for="l in primaryLinks"
                :key="l.to"
                :to="l.to"
                class="dz-bottom-nav-item"
                :class="{ 'is-active': isActiveRoute(l.name) }"
            >
                <span class="inline-flex h-5 w-5 items-center justify-center">
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true">
                        <path :d="iconPath(l.icon)" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <span class="text-[11px]">{{ l.shortLabel }}</span>
            </RouterLink>
            <button
                type="button"
                class="dz-bottom-nav-item"
                :class="{ 'is-active': mobileOpen || isMoreActive }"
                @click="mobileOpen = !mobileOpen"
            >
                <span class="text-base">☰</span>
                <span class="text-[11px]">Mais</span>
            </button>
        </nav>

        <ToastStack />
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import ToastStack from '../components/ToastStack.vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const mobileOpen = ref(false);

const links = [
    { to: '/', name: 'dashboard', label: 'Início', shortLabel: 'Início', icon: 'home' },
    { to: '/transacoes', name: 'transactions', label: 'Transações', shortLabel: 'Lanç.', icon: 'wallet' },
    { to: '/dividas', name: 'debts', label: 'Dívidas', shortLabel: 'Dívidas', icon: 'receipt' },
    { to: '/recomendacoes', name: 'recommendations', label: 'Recomendações', shortLabel: 'Ações', icon: 'target' },
    { to: '/previsao', name: 'projection', label: 'Previsão de caixa', shortLabel: 'Previsão', icon: 'chart' },
    { to: '/investimentos', name: 'investments', label: 'Investimentos', shortLabel: 'Invest.', icon: 'briefcase' },
    { to: '/contas', name: 'accounts', label: 'Contas', shortLabel: 'Contas', icon: 'bank' },
    { to: '/recorrencias', name: 'recurrence', label: 'Recorrências', shortLabel: 'Recorr.', icon: 'repeat' },
    { to: '/assinatura', name: 'billing', label: 'Assinatura', shortLabel: 'Plano', icon: 'card' },
];

const primaryLinks = links.slice(0, 4);
const primaryNames = new Set(primaryLinks.map((l) => l.name));

const currentLabel = computed(() => {
    const found = links.find((l) => l.name === route.name);
    return found?.label ?? 'Bolso Planejado';
});

const isMoreActive = computed(() => !primaryNames.has(String(route.name ?? '')));

function isActiveRoute(name) {
    return route.name === name;
}

function iconPath(name) {
    const icons = {
        home: 'M3 11.5L12 4l9 7.5M5 10v9h5v-5h4v5h5v-9',
        wallet: 'M3.5 7.5h17v10a2 2 0 0 1-2 2h-13a2 2 0 0 1-2-2v-10Zm0 0 2-3h11l2 3M15.5 12.5h3',
        receipt: 'M7 4h10v16l-2-1.5L13 20l-2-1.5L9 20l-2-1.5L5 20V4h2Zm2 4h6m-6 4h6',
        target: 'M12 3v3m0 12v3m9-9h-3M6 12H3m15.364 6.364-2.121-2.121M7.757 7.757 5.636 5.636m12.728 0-2.121 2.121M7.757 16.243l-2.121 2.121M12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z',
        chart: 'M4 19h16M7 15l3-3 3 2 4-5',
        briefcase: 'M9 6V4h6v2m-10 0h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm7 6h2',
        bank: 'M3 9h18M5 9v8m4-8v8m6-8v8m4-8v8M3 20h18M12 3l9 4H3l9-4Z',
        repeat: 'M17 7h3V4M7 17H4v3m0-3 4-4m12-2-4-4M7 7h9a3 3 0 0 1 3 3m-14 4a3 3 0 0 0 3 3h9',
        card: 'M4 4h16v4H4V4Zm0 6h16v10H4V10Zm3 3h4v4H7v-4Z',
    };
    return icons[name] ?? icons.home;
}

async function sair() {
    mobileOpen.value = false;
    await auth.logout();
    router.push({ name: 'login' });
}
</script>
