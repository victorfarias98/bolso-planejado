<template>
    <Modal
        :show="show"
        title="Check-in diário"
        @update:show="emit('update:show', $event)"
        @close="emit('close')"
    >
        <form
            id="daily-checkin-form"
            class="grid gap-3"
            @submit.prevent="submit"
        >
            <p class="text-xs text-slate-500">
                Registre um lançamento de hoje em poucos segundos.
            </p>
            <div>
                <label class="text-xs text-slate-500">Conta</label>
                <select
                    v-model="form.financial_account_id"
                    required
                    class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                >
                    <option
                        disabled
                        value=""
                    >
                        Selecione
                    </option>
                    <option
                        v-for="a in accounts"
                        :key="a.id"
                        :value="a.id"
                    >
                        {{ a.name }}
                    </option>
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-500">Tipo</label>
                <select
                    v-model="form.type"
                    class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                >
                    <option value="income">
                        Entrada
                    </option>
                    <option value="expense">
                        Saída
                    </option>
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-500">Categoria</label>
                <div class="mt-1 flex gap-2">
                    <select
                        v-model="form.category_id"
                        required
                        class="min-w-0 flex-1 rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                    >
                        <option
                            disabled
                            value=""
                        >
                            Selecione
                        </option>
                        <option
                            v-for="c in categories"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.name }}
                        </option>
                    </select>
                    <button
                        type="button"
                        class="shrink-0 rounded-lg border border-slate-700 px-2 py-2 text-xs text-slate-300 hover:bg-slate-800 whitespace-nowrap"
                        @click="openNewCategoryModal"
                    >
                        Nova
                    </button>
                </div>
            </div>
            <div>
                <label class="text-xs text-slate-500">Valor</label>
                <input
                    v-model="form.amount"
                    type="number"
                    min="0.01"
                    step="0.01"
                    required
                    class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                >
            </div>
            <div>
                <label class="text-xs text-slate-500">Descrição (opcional)</label>
                <input
                    v-model="form.description"
                    class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                    placeholder="Ex.: almoço, pix, gasolina..."
                >
            </div>
            <p
                v-if="error"
                class="text-sm text-rose-400"
            >
                {{ error }}
            </p>
        </form>
        <template #footer>
            <div class="flex justify-end gap-2">
                <button
                    type="button"
                    class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300 hover:bg-slate-800"
                    @click="emit('update:show', false)"
                >
                    Cancelar
                </button>
                <button
                    type="submit"
                    form="daily-checkin-form"
                    class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-500 disabled:opacity-50"
                    :disabled="saving"
                >
                    {{ saving ? 'Salvando…' : 'Salvar lançamento' }}
                </button>
            </div>
        </template>
    </Modal>

    <Modal
        v-model:show="newCategoryModalOpen"
        title="Nova categoria"
    >
        <div>
            <label class="text-xs text-slate-500">Nome</label>
            <input
                v-model="newCategoryName"
                type="text"
                maxlength="255"
                class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100"
                placeholder="Ex.: Educação, pets…"
                @keydown.enter.prevent="submitNewCategory"
            >
            <p
                v-if="newCategoryError"
                class="mt-2 text-sm text-rose-400"
            >
                {{ newCategoryError }}
            </p>
        </div>
        <template #footer>
            <div class="flex justify-end gap-2">
                <button
                    type="button"
                    class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300 hover:bg-slate-800"
                    @click="newCategoryModalOpen = false"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-500 disabled:opacity-50"
                    :disabled="creatingCategory"
                    @click="submitNewCategory"
                >
                    {{ creatingCategory ? 'Salvando…' : 'Criar' }}
                </button>
            </div>
        </template>
    </Modal>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';
import { createCategory } from '../api/categories';
import { http } from '../api/http';
import Modal from './Modal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    accounts: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    userId: {
        type: String,
        default: 'anon',
    },
    refreshCategories: {
        type: Function,
        default: null,
    },
});

const emit = defineEmits(['update:show', 'saved', 'close']);

const saving = ref(false);
const error = ref('');
const newCategoryModalOpen = ref(false);
const newCategoryName = ref('');
const creatingCategory = ref(false);
const newCategoryError = ref('');
const form = reactive({
    financial_account_id: '',
    category_id: '',
    type: 'expense',
    amount: '',
    description: '',
});

function todayStr() {
    return new Date().toISOString().slice(0, 10);
}

function checkinKey() {
    return `dz_checkin_date_${props.userId || 'anon'}`;
}

function openNewCategoryModal() {
    newCategoryName.value = '';
    newCategoryError.value = '';
    newCategoryModalOpen.value = true;
}

async function submitNewCategory() {
    newCategoryError.value = '';
    const name = newCategoryName.value.trim();
    if (!name) {
        newCategoryError.value = 'Informe o nome da categoria.';
        return;
    }
    creatingCategory.value = true;
    try {
        const created = await createCategory({ name });
        if (props.refreshCategories) {
            await props.refreshCategories();
        }
        form.category_id = created.id;
        newCategoryModalOpen.value = false;
    } catch (e) {
        const errs = e.response?.data?.errors;
        newCategoryError.value = errs
            ? Object.values(errs).flat().join(' ')
            : e.response?.data?.message || 'Não foi possível criar a categoria.';
    } finally {
        creatingCategory.value = false;
    }
}

watch(
    () => props.show,
    (isOpen) => {
        if (!isOpen) {
            return;
        }
        error.value = '';
        if (!form.financial_account_id && props.accounts.length > 0) {
            form.financial_account_id = props.accounts[0].id;
        }
        if (!form.category_id && props.categories.length > 0) {
            form.category_id = props.categories[0].id;
        }
    },
);

async function submit() {
    error.value = '';
    saving.value = true;
    try {
        await http.post('/transactions', {
            financial_account_id: form.financial_account_id,
            category_id: form.category_id,
            type: form.type,
            amount: form.amount,
            occurred_on: todayStr(),
            status: 'completed',
            description: form.description || null,
        });
        localStorage.setItem(checkinKey(), todayStr());
        form.amount = '';
        form.description = '';
        emit('saved');
        emit('update:show', false);
    } catch (e) {
        const errs = e.response?.data?.errors;
        error.value = errs ? Object.values(errs).flat().join(' ') : 'Não foi possível salvar.';
    } finally {
        saving.value = false;
    }
}
</script>
