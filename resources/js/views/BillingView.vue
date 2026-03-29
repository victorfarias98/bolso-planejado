<template>
    <div>
        <h1 class="text-2xl font-semibold text-white">
            Plano e assinatura
        </h1>
        <p class="mt-1 text-slate-400">
            Plano atual: <span class="text-slate-200">{{ auth.user?.billing?.plan?.name ?? '—' }}</span>
            <span v-if="auth.user?.billing?.premium_expires_at" class="block text-sm">
                Renovação / referência: {{ formatDate(auth.user.billing.premium_expires_at) }}
            </span>
        </p>

        <div
            v-if="route.query.locked"
            class="mt-4 rounded-lg border border-amber-800/80 bg-amber-950/40 px-4 py-3 text-sm text-amber-100"
        >
            Este recurso faz parte do plano Premium. Escolha um plano abaixo ou use o checkout simulado (ambiente de desenvolvimento).
        </div>

        <div v-if="loading" class="mt-8 text-sm text-slate-500">
            Carregando planos…
        </div>

        <div v-else class="mt-8 grid gap-4 sm:grid-cols-2">
            <article
                v-for="p in plans"
                :key="p.id"
                class="rounded-xl border border-slate-800 bg-slate-900/50 p-5"
            >
                <h2 class="text-lg font-semibold text-white">{{ p.name }}</h2>
                <p class="mt-2 text-2xl font-bold text-emerald-300">
                    {{ formatMoney(p.price_cents, p.currency) }}
                    <span v-if="p.interval === 'month'" class="text-sm font-normal text-slate-500">/mês</span>
                    <span v-else-if="p.interval === 'year'" class="text-sm font-normal text-slate-500">/ano</span>
                </p>
                <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">
                    {{ modeLabel(p.billing_mode) }}
                </p>
                <button
                    type="button"
                    class="mt-4 w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-500 disabled:opacity-50"
                    :disabled="checkoutLoading === p.slug"
                    @click="checkout(p.slug)"
                >
                    {{ checkoutLoading === p.slug ? 'Processando…' : 'Ativar (checkout simulado)' }}
                </button>
            </article>
        </div>

        <p v-if="message" class="mt-6 text-sm text-emerald-400">
            {{ message }}
        </p>
        <p v-if="error" class="mt-2 text-sm text-rose-400">
            {{ error }}
        </p>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { http } from '../api/http';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const route = useRoute();
const plans = ref([]);
const loading = ref(true);
const checkoutLoading = ref(null);
const message = ref('');
const error = ref('');

function formatMoney(cents, currency) {
    const n = Number(cents) / 100;
    return n.toLocaleString('pt-BR', { style: 'currency', currency: currency || 'BRL' });
}

function formatDate(iso) {
    try {
        return new Date(iso).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
    } catch {
        return iso;
    }
}

function modeLabel(mode) {
    const m = {
        subscription: 'Assinatura',
        one_time: 'Pagamento único',
        free: 'Gratuito',
    };
    return m[mode] ?? mode;
}

onMounted(async () => {
    error.value = '';
    try {
        const { data } = await http.get('/billing/plans');
        plans.value = data.data ?? [];
    } catch (e) {
        error.value = e.response?.data?.message ?? 'Não foi possível carregar os planos.';
    } finally {
        loading.value = false;
    }
});

async function checkout(slug) {
    message.value = '';
    error.value = '';
    checkoutLoading.value = slug;
    try {
        await http.post('/billing/checkout', { plan_slug: slug });
        message.value = 'Plano aplicado com sucesso (modo simulado).';
        await auth.fetchMe();
    } catch (e) {
        error.value = e.response?.data?.message
            ?? (e.response?.data?.errors?.plan_slug?.[0])
            ?? 'Não foi possível concluir o checkout.';
    } finally {
        checkoutLoading.value = null;
    }
}
</script>
