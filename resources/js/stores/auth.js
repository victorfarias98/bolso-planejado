import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { http, TOKEN_KEY } from '../api/http';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const token = ref(localStorage.getItem(TOKEN_KEY));
    const loading = ref(false);
    const error = ref(null);

    const isAuthenticated = computed(() => Boolean(token.value));

    function setToken(newToken) {
        token.value = newToken;
        if (newToken) {
            localStorage.setItem(TOKEN_KEY, newToken);
        } else {
            localStorage.removeItem(TOKEN_KEY);
        }
    }

    function formatError(e, fallbackMessage = 'Ocorreu um erro. Tente novamente.') {
        const status = e?.response?.status;
        const data = e?.response?.data;
        const errors = data?.errors;
        if (status === 422) {
            // Mensagens amigáveis por campo/regra mais comuns
            const messages = [];
            if (errors) {
                const mapField = (field, arr) => {
                    const joined = Array.isArray(arr) ? arr.join(' ') : String(arr);
                    // Reescritas conhecidas
                    if (field === 'email' && /unique/i.test(joined)) {
                        return 'Este e-mail já está em uso.';
                    }
                    if (field === 'email') {
                        return 'Informe um e-mail válido.';
                    }
                    if (field === 'password' && /confirmed/i.test(joined)) {
                        return 'As senhas não conferem.';
                    }
                    if (field === 'password' && /min/i.test(joined)) {
                        return 'A senha deve ter no mínimo 8 caracteres.';
                    }
                    if (field === 'name' && /required/i.test(joined)) {
                        return 'Informe seu nome.';
                    }
                    return joined;
                };
                for (const [field, arr] of Object.entries(errors)) {
                    messages.push(mapField(field, arr));
                }
                if (messages.length) return messages.join(' ');
            }
            // Fallback 422 sem details
            return data?.message ?? fallbackMessage;
        }
        return data?.message ?? fallbackMessage;
    }

    async function fetchMe() {
        if (! token.value) {
            return;
        }
        const { data } = await http.get('/me');
        user.value = data.data;
    }

    /** @param {string} featureKey */
    function hasFeature(featureKey) {
        return user.value?.billing?.entitlements?.[featureKey]?.enabled === true;
    }

    async function hydrate() {
        const stored = localStorage.getItem(TOKEN_KEY);
        if (stored) {
            token.value = stored;
            try {
                await fetchMe();
            } catch {
                setToken(null);
                user.value = null;
            }
        }
    }

    async function login(payload) {
        loading.value = true;
        error.value = null;
        try {
            const { data } = await http.post('/login', payload);
            setToken(data.token);
            user.value = data.user;
            return true;
        } catch (e) {
            // Mensagem clara para credenciais inválidas
            if (e?.response?.status === 422) {
                error.value = 'E-mail ou senha inválidos.';
            } else {
                error.value = formatError(e, 'Não foi possível entrar.');
            }
            return false;
        } finally {
            loading.value = false;
        }
    }

    async function register(payload) {
        loading.value = true;
        error.value = null;
        try {
            const { data } = await http.post('/register', payload);
            setToken(data.token);
            user.value = data.user;
            return true;
        } catch (e) {
            error.value = formatError(e, 'Não foi possível cadastrar.');
            return false;
        } finally {
            loading.value = false;
        }
    }

    async function logout() {
        try {
            await http.post('/logout');
        } catch {
            //
        }
        setToken(null);
        user.value = null;
    }

    return {
        user,
        token,
        loading,
        error,
        isAuthenticated,
        hydrate,
        login,
        register,
        logout,
        fetchMe,
        hasFeature,
    };
});
