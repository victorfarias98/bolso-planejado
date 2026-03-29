<template>
    <div class="flex min-h-screen items-center justify-center bg-slate-950 px-4">
        <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900/50 p-8 shadow-xl">
            <h1 class="text-center text-xl font-semibold text-white">Entrar</h1>
            <p class="mt-1 text-center text-sm text-slate-400">Organize dívidas e veja seu saldo futuro</p>
            <p
                v-if="isDev"
                class="mt-3 rounded-lg border border-slate-800 bg-slate-950/80 px-3 py-2 text-center text-xs text-slate-500"
            >
                Conta demo:
                <span class="font-mono text-slate-400">demo@dividazero.local</span>
                · senha
                <span class="font-mono text-slate-400">demo1234</span>
            </p>
            <button
                v-if="isDev"
                type="button"
                class="mt-3 w-full rounded-lg border border-emerald-700/70 bg-emerald-900/30 px-4 py-2.5 text-sm font-medium text-emerald-200 transition hover:bg-emerald-900/50 disabled:opacity-50"
                :disabled="auth.loading"
                @click="loginDemo"
            >
                Entrar no demo com 1 clique
            </button>

            <form class="mt-8 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="block text-xs font-medium text-slate-400">E-mail</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        autocomplete="email"
                    >
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Senha</label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        autocomplete="current-password"
                    >
                </div>
                <p
                    v-if="auth.error"
                    class="text-sm text-rose-400"
                >
                    {{ auth.error }}
                </p>
                <button
                    type="submit"
                    class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-500 disabled:opacity-50"
                    :disabled="auth.loading"
                >
                    {{ auth.loading ? 'Entrando…' : 'Entrar' }}
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                Não tem conta?
                <RouterLink
                    :to="{ name: 'register' }"
                    class="text-emerald-400 hover:underline"
                >Cadastre-se</RouterLink>
            </p>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const isDev = import.meta.env.DEV;

const form = reactive({
    email: '',
    password: '',
});

async function submit() {
    const ok = await auth.login({ email: form.email, password: form.password });
    if (ok) {
        const redirect = route.query.redirect ?? '/';
        router.push(typeof redirect === 'string' ? redirect : '/');
    }
}

async function loginDemo() {
    form.email = 'demo@dividazero.local';
    form.password = 'demo1234';
    await submit();
}
</script>
