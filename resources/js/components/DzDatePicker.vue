<template>
    <input
        ref="inputRef"
        type="text"
        readonly
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :class="combinedClass"
        autocomplete="off"
    >
</template>

<script setup>
import flatpickr from 'flatpickr';
import { Portuguese } from 'flatpickr/dist/esm/l10n/pt.js';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    minDate: {
        type: String,
        default: null,
    },
    maxDate: {
        type: String,
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: 'Selecione a data',
    },
    inputClass: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    quickActions: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:modelValue']);

const inputRef = ref(null);
let fp = null;
let quickActionsEl = null;

const baseClass = 'dz-input cursor-pointer';
const combinedClass = computed(() => [baseClass, props.inputClass].filter(Boolean).join(' '));

function buildConfig() {
    return {
        dateFormat: 'Y-m-d',
        locale: Portuguese,
        allowInput: false,
        disableMobile: true,
        clickOpens: true,
        minDate: props.minDate || undefined,
        maxDate: props.maxDate || undefined,
        defaultDate: props.modelValue || undefined,
        onChange: (_selectedDates, dateStr) => {
            emit('update:modelValue', dateStr || '');
        },
        onReady: () => {
            renderQuickActions();
        },
    };
}

function parseIso(iso) {
    if (!iso) return null;
    const dt = new Date(`${iso}T12:00:00`);
    return Number.isNaN(dt.getTime()) ? null : dt;
}

function toIso(dt) {
    return dt.toISOString().slice(0, 10);
}

function clampIso(iso) {
    let target = parseIso(iso);
    if (!target) return null;
    const min = parseIso(props.minDate);
    const max = parseIso(props.maxDate);
    if (min && target < min) target = min;
    if (max && target > max) target = max;
    return toIso(target);
}

function addDays(base, days) {
    const dt = new Date(base);
    dt.setDate(dt.getDate() + days);
    return dt;
}

function addMonths(base, months) {
    const dt = new Date(base);
    dt.setMonth(dt.getMonth() + months);
    return dt;
}

function applyQuickDate(calc) {
    if (!fp) return;
    const base = new Date();
    base.setHours(12, 0, 0, 0);
    const target = calc(base);
    const iso = clampIso(toIso(target));
    if (!iso) return;
    fp.setDate(iso, true);
}

function renderQuickActions() {
    if (!fp || !fp.calendarContainer) return;
    if (quickActionsEl) {
        quickActionsEl.remove();
        quickActionsEl = null;
    }
    if (!props.quickActions) return;

    const wrap = document.createElement('div');
    wrap.className = 'dz-fp-quick-actions';

    const actions = [
        { label: 'Hoje', run: (d) => d },
        { label: 'Amanhã', run: (d) => addDays(d, 1) },
        { label: '+7 dias', run: (d) => addDays(d, 7) },
        { label: '+30 dias', run: (d) => addDays(d, 30) },
        { label: 'Próx. mês', run: (d) => addMonths(d, 1) },
    ];

    for (const action of actions) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'dz-fp-quick-btn';
        btn.textContent = action.label;
        btn.addEventListener('click', () => applyQuickDate(action.run));
        wrap.appendChild(btn);
    }

    fp.calendarContainer.appendChild(wrap);
    quickActionsEl = wrap;
}

onMounted(() => {
    if (!inputRef.value) {
        return;
    }
    fp = flatpickr(inputRef.value, buildConfig());
});

watch(
    () => [props.minDate, props.maxDate],
    () => {
        if (!fp) {
            return;
        }
        fp.set('minDate', props.minDate || null);
        fp.set('maxDate', props.maxDate || null);
        renderQuickActions();
    },
);

watch(
    () => props.modelValue,
    (v) => {
        if (!fp) {
            return;
        }
        if (!v) {
            fp.clear();
            return;
        }
        const cur = fp.selectedDates[0];
        const str = cur ? fp.formatDate(cur, 'Y-m-d') : '';
        if (str !== v) {
            fp.setDate(v, false);
        }
    },
);

watch(
    () => props.disabled,
    (v) => {
        if (inputRef.value) {
            inputRef.value.disabled = v;
        }
        if (!fp) {
            return;
        }
        if (v) {
            fp.close();
        }
    },
);

watch(
    () => props.quickActions,
    () => {
        renderQuickActions();
    },
);

onBeforeUnmount(() => {
    if (fp) {
        fp.destroy();
        fp = null;
    }
    quickActionsEl = null;
});
</script>
