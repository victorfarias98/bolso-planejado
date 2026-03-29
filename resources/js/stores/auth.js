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
            error.value = e.response?.data?.message ?? 'Não foi possível entrar.';
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
            const errs = e.response?.data?.errors;
            error.value = errs
                ? Object.values(errs).flat().join(' ')
                : (e.response?.data?.message ?? 'Não foi possível cadastrar.');
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
