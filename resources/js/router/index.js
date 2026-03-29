import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/LoginView.vue'),
        meta: { guest: true },
    },
    {
        path: '/cadastro',
        name: 'register',
        component: () => import('../views/RegisterView.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        component: () => import('../layouts/MainLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('../views/DashboardView.vue'),
            },
            {
                path: 'contas',
                name: 'accounts',
                component: () => import('../views/AccountsView.vue'),
            },
            {
                path: 'transacoes',
                name: 'transactions',
                component: () => import('../views/TransactionsView.vue'),
            },
            {
                path: 'dividas',
                name: 'debts',
                component: () => import('../views/DebtsView.vue'),
            },
            {
                path: 'recorrencias',
                name: 'recurrence',
                component: () => import('../views/RecurrenceView.vue'),
            },
            {
                path: 'previsao',
                name: 'projection',
                component: () => import('../views/ProjectionView.vue'),
            },
            {
                path: 'investimentos',
                name: 'investments',
                meta: { requiresFeature: 'investments' },
                component: () => import('../views/InvestmentsView.vue'),
            },
            {
                path: 'recomendacoes',
                name: 'recommendations',
                meta: { requiresFeature: 'recommendations' },
                component: () => import('../views/RecommendationsView.vue'),
            },
            {
                path: 'assinatura',
                name: 'billing',
                component: () => import('../views/BillingView.vue'),
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory('/app'),
    routes,
    scrollBehavior: () => ({ top: 0 }),
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();
    const needAuth = to.matched.some((r) => r.meta.requiresAuth);

    if (needAuth && ! auth.token) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guest && auth.token) {
        return { name: 'dashboard' };
    }

    if (needAuth && auth.token && ! auth.user) {
        try {
            await auth.fetchMe();
        } catch {
            await auth.logout();
            return { name: 'login', query: { redirect: to.fullPath } };
        }
    }

    const featureGate = to.matched.find((r) => r.meta.requiresFeature)?.meta.requiresFeature;
    if (needAuth && featureGate && auth.user && ! auth.hasFeature(featureGate)) {
        return { name: 'billing', query: { locked: String(featureGate) } };
    }
});

export default router;
