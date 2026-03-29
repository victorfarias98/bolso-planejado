<template>
    <div>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-white">
                    Contas
                </h1>
                <p class="mt-1 text-slate-400">
                    Carteiras usadas na projeção de saldo (saldo inicial + lançamentos).
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="dz-btn dz-btn-primary px-4 text-sm font-medium" @click="abrirNova">Nova conta</button>
                <button type="button" class="dz-btn dz-btn-ghost px-4 text-sm font-medium" @click="abrirAdicaoEmMassa">Adição em massa</button>
            </div>
        </div>
        <button
            type="button"
            class="dz-fab md:hidden"
            aria-label="Nova conta"
            @click="abrirNova"
        >
            +
        </button>
        <BulkSelectionBar
            v-if="selectedCount"
            :selected-count="selectedCount"
            singular-label="conta selecionada"
            plural-label="contas selecionadas"
            action-label="Excluir selecionadas"
            @action="abrirExclusaoSelecionadas"
        />

        <div class="mt-8 space-y-3 md:hidden">
            <template v-if="loading">
                <div class="dz-skeleton h-24 w-full rounded-xl" />
                <div class="dz-skeleton h-24 w-full rounded-xl" />
                <div class="dz-skeleton h-24 w-full rounded-xl" />
            </template>
            <template v-else>
                <div
                    v-for="a in displayedAccounts"
                    :key="a.id"
                    class="dz-mobile-card rounded-xl border border-slate-800 bg-slate-900/40 p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-white">{{ a.name }}</p>
                            <p class="mt-1 text-xs text-slate-500">Cadastro: {{ formatIsoDate(a.created_at) }}</p>
                        </div>
                        <input
                            type="checkbox"
                            :checked="selectedIds.includes(a.id)"
                            class="rounded border-slate-600 bg-slate-950 text-emerald-500"
                            @change="toggleSelection(a.id)"
                        >
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <p class="text-slate-400">Saldo inicial</p>
                        <p class="text-right text-slate-200">{{ Number(a.initial_balance).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}</p>
                        <p class="text-slate-400">Moeda</p>
                        <p class="text-right text-slate-300">{{ a.currency }}</p>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button type="button" class="dz-btn dz-btn-ghost flex-1 text-xs" @click="iniciarEdicao(a)">Editar</button>
                        <button type="button" class="dz-btn flex-1 border border-rose-800/60 text-xs text-rose-300 hover:bg-rose-950/30" @click="remover(a.id)">Excluir</button>
                    </div>
                </div>
                <div v-if="!displayedAccounts.length" class="rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-8 text-center text-slate-500">
                    Nenhuma conta cadastrada.
                </div>
            </template>
        </div>

        <div class="mt-8 hidden overflow-x-auto rounded-xl border border-slate-800 transition-colors duration-300 hover:border-slate-700 md:block">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-800 bg-slate-900/60 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3"><input type="checkbox" :checked="allVisibleSelected" class="rounded border-slate-600 bg-slate-950 text-emerald-500" @change="toggleSelectAllVisible"></th>
                        <SortableTh
                            column="created_at"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Cadastro
                        </SortableTh>
                        <SortableTh
                            column="name"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Nome
                        </SortableTh>
                        <SortableTh
                            column="initial_balance"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Saldo inicial
                        </SortableTh>
                        <SortableTh
                            column="currency"
                            :model-key="sortKey"
                            :model-dir="sortDir"
                            @sort="onSort"
                        >
                            Moeda
                        </SortableTh>
                        <th class="px-4 py-3 text-right">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="loading">
                        <tr>
                            <td
                                colspan="6"
                                class="dz-loading-pulse px-4 py-10 text-center text-slate-500"
                            >
                                Carregando contas…
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr
                            v-for="a in displayedAccounts"
                            :key="a.id"
                            class="border-b border-slate-800/80 transition-colors duration-200"
                            :class="'hover:bg-slate-800/30'"
                        >
                            <td class="px-4 py-3"><input type="checkbox" :checked="selectedIds.includes(a.id)" class="rounded border-slate-600 bg-slate-950 text-emerald-500" @change="toggleSelection(a.id)"></td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ formatIsoDate(a.created_at) }}</td>
                            <td class="px-4 py-3 font-medium text-white">{{ a.name }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ Number(a.initial_balance).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ a.currency }}</td>
                            <td class="px-4 py-3 text-right"><button type="button" class="mr-3 text-xs font-medium text-emerald-400/90 transition hover:text-emerald-300" @click="iniciarEdicao(a)">Editar</button><button type="button" class="text-xs text-rose-400/90 transition hover:text-rose-300" @click="remover(a.id)">Excluir</button></td>
                        </tr>
                        <tr v-if="!displayedAccounts.length && !loading">
                            <td
                                colspan="6"
                                class="px-4 py-8 text-center text-slate-500"
                            >
                                Nenhuma conta cadastrada.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-xs text-slate-500">
            Alterar o saldo inicial recalcula a projeção; não altera lançamentos já registrados.
        </p>

        <Modal v-model:show="formModalOpen" title="Nova conta">
            <form id="form-conta" class="grid gap-3" @submit.prevent="criar">
                <div><label class="text-xs text-slate-500">Nome</label><input v-model="form.name" required class="dz-input mt-1 w-full"></div>
                <div><label class="text-xs text-slate-500">Saldo inicial</label><input v-model="form.initial_balance" type="number" step="0.01" class="dz-input mt-1 w-full"></div>
                <p v-if="formError" class="text-sm text-rose-400">{{ formError }}</p>
            </form>
            <template #footer><div class="flex w-full gap-2 sm:justify-end"><button type="button" class="dz-btn dz-btn-ghost flex-1 sm:flex-none" @click="formModalOpen = false">Cancelar</button><button type="submit" form="form-conta" class="dz-btn dz-btn-primary flex-1 sm:flex-none disabled:opacity-50" :disabled="saving">{{ saving ? 'Salvando…' : 'Salvar' }}</button></div></template>
        </Modal>

        <Modal v-model:show="editModalOpen" title="Editar conta">
            <div class="grid gap-3">
                <div><label class="text-xs text-slate-500">Nome</label><input v-model="editDraft.name" class="dz-input mt-1 w-full"></div>
                <div><label class="text-xs text-slate-500">Saldo inicial</label><input v-model="editDraft.initial_balance" type="number" step="0.01" class="dz-input mt-1 w-full"></div>
                <div><label class="text-xs text-slate-500">Moeda</label><select v-model="editDraft.currency" class="dz-select mt-1 w-full"><option value="BRL">BRL</option><option value="USD">USD</option></select></div>
            </div>
            <template #footer><div class="flex w-full gap-2 sm:justify-end"><button type="button" class="dz-btn dz-btn-ghost flex-1 sm:flex-none" @click="cancelarEdicao">Cancelar</button><button type="button" class="dz-btn dz-btn-primary flex-1 sm:flex-none disabled:opacity-50" :disabled="savingEdit" @click="salvarEdicao(editingId)">{{ savingEdit ? 'Salvando…' : 'Salvar' }}</button></div></template>
        </Modal>

        <Modal v-model:show="deleteModalOpen" title="Excluir contas?">
            <p class="text-sm text-slate-400">Esta ação remove também dados vinculados.</p>
            <p class="mt-3 rounded-lg border border-slate-800 bg-slate-950/80 px-3 py-2 text-sm font-medium text-white">{{ deleteTargetLabel }}</p>
            <template #footer><div class="flex w-full gap-2 sm:justify-end"><button type="button" class="dz-btn dz-btn-ghost flex-1 sm:flex-none" :disabled="deleting" @click="deleteModalOpen = false">Cancelar</button><button type="button" class="dz-btn flex-1 border border-rose-700 bg-rose-600 text-white hover:bg-rose-500 sm:flex-none disabled:opacity-50" :disabled="deleting" @click="confirmarExclusao">{{ deleting ? 'Excluindo…' : 'Excluir' }}</button></div></template>
        </Modal>

        <Modal v-model:show="bulkModalOpen" title="Adicionar contas em massa">
            <div class="space-y-2">
                <div v-for="(row, idx) in bulkRows" :key="idx" class="grid gap-2 rounded-lg border border-slate-800 bg-slate-950/60 p-3 md:grid-cols-4">
                    <input v-model="row.name" class="dz-input md:col-span-2" placeholder="Nome *">
                    <input v-model="row.initial_balance" type="number" step="0.01" class="dz-input" placeholder="Saldo inicial">
                    <button type="button" class="dz-btn dz-btn-ghost text-xs" @click="removeBulkRow(idx)">Remover</button>
                </div>
            </div>
            <button type="button" class="dz-btn dz-btn-ghost mt-2 text-xs" @click="addBulkRow">+ Adicionar linha</button>
            <p v-if="formError" class="mt-2 text-sm text-rose-400">{{ formError }}</p>
            <template #footer><div class="flex w-full gap-2 sm:justify-end"><button type="button" class="dz-btn dz-btn-ghost flex-1 sm:flex-none" @click="bulkModalOpen = false">Cancelar</button><button type="button" class="dz-btn dz-btn-primary flex-1 sm:flex-none disabled:opacity-50" :disabled="saving" @click="salvarEmMassa">{{ saving ? 'Salvando…' : 'Salvar em massa' }}</button></div></template>
        </Modal>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import BulkSelectionBar from '../components/BulkSelectionBar.vue';
import Modal from '../components/Modal.vue';
import { useToastStore } from '../stores/toast';
import SortableTh from '../components/SortableTh.vue';
import { useAccounts } from '../composables/useAccounts';

const toast = useToastStore();
const {
    sortKey,
    sortDir,
    displayedAccounts,
    onSort,
    loading,
    saving,
    savingEdit,
    deleting,
    formError,
    editingId,
    formModalOpen,
    editModalOpen,
    deleteModalOpen,
    bulkModalOpen,
    selectedIds,
    selectedCount,
    allVisibleSelected,
    bulkRows,
    deleteTargetLabel,
    editDraft,
    form,
    carregar,
    abrirNova,
    abrirAdicaoEmMassa,
    addBulkRow,
    removeBulkRow,
    salvarEmMassa,
    toggleSelection,
    toggleSelectAllVisible,
    abrirExclusaoSelecionadas,
    iniciarEdicao,
    cancelarEdicao,
    salvarEdicao,
    criar,
    remover,
    confirmarExclusao,
} = useAccounts(toast);

function formatIsoDate(iso) {
    if (! iso) {
        return '—';
    }
    return new Date(iso).toLocaleDateString('pt-BR');
}
onMounted(carregar);
</script>
