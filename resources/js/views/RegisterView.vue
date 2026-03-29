<template>
    <div class="flex min-h-screen items-center justify-center bg-slate-950 px-4 py-12">
        <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900/50 p-8 shadow-xl">
            <h1 class="text-center text-xl font-semibold text-white">Criar conta</h1>
            <p class="mt-1 text-center text-sm text-slate-400">Comece com previsão de caixa e controle de gastos</p>

            <form class="mt-8 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="block text-xs font-medium text-slate-400">Nome</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        autocomplete="name"
                    >
                </div>
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
                        autocomplete="new-password"
                    >
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Confirmar senha</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        autocomplete="new-password"
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
                    {{ auth.loading ? 'Criando…' : 'Cadastrar' }}
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                Já tem conta?
                <RouterLink
                    :to="{ name: 'login' }"
                    class="text-emerald-400 hover:underline"
                >Entrar</RouterLink>
            </p>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

async function submit() {
    const ok = await auth.register({ ...form });
    if (ok) {
        router.push({ name: 'dashboard' });
    }
}
</script>
