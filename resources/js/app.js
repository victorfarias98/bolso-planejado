import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import { useAuthStore } from './stores/auth';
import '../css/app.css';

const mountEl = document.querySelector('#app');
if (mountEl) {
    const app = createApp(App);
    const pinia = createPinia();

    app.use(pinia);
    app.use(router);

    const auth = useAuthStore();
    auth.hydrate().finally(() => {
        app.mount('#app');
    });
}
