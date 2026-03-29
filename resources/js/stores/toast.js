import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useToastStore = defineStore('toast', () => {
    const items = ref([]);
    let seq = 0;

    function dismiss(id) {
        items.value = items.value.filter((t) => t.id !== id);
    }

    /**
     * @param {'success'|'error'|'info'} variant
     */
    function show(message, variant = 'success', duration = 4200) {
        const id = ++seq;
        items.value.push({ id, message, variant });
        if (duration > 0) {
            setTimeout(() => dismiss(id), duration);
        }

        return id;
    }

    function success(message, duration) {
        return show(message, 'success', duration);
    }

    function error(message, duration) {
        return show(message, 'error', duration);
    }

    function info(message, duration) {
        return show(message, 'info', duration);
    }

    return {
        items,
        show,
        dismiss,
        success,
        error,
        info,
    };
});
