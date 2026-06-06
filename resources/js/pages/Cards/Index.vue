<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface Card {
    id: number;
    card_number: string;
    pin: string;
    activation_date: string;
    expiration_date: string;
    balance: string;
    status: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedCards {
    data: Card[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    cards: PaginatedCards;
    filters: {
        search?: string;
        filter?: string;
        sort_by?: string;
        sort_order?: string;
    };
}>();

const search = ref(props.filters.search ?? '');
const filter = ref(props.filters.filter ?? 'all');
const sortBy = ref(props.filters.sort_by ?? 'created_at');
const sortOrder = ref(props.filters.sort_order ?? 'desc');

function updateData() {
    const params: Record<string, string> = {};
    if (search.value) params.search = search.value;
    if (filter.value && filter.value !== 'all') params.filter = filter.value;
    if (sortBy.value) params.sort_by = sortBy.value;
    if (sortOrder.value) params.sort_order = sortOrder.value;

    router.get('/cards', params, {
        preserveState: true,
        replace: true,
    });
}

let timeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        updateData();
    }, 300);
});

watch(filter, () => {
    updateData();
});

function toggleSort(column: string) {
    if (sortBy.value === column) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortOrder.value = 'asc';
    }
    updateData();
}

function statusClass(status: string) {
    switch (status) {
        case 'Active':
            return 'bg-emerald-500/15 text-emerald-500 border-emerald-500/25 dark:bg-emerald-500/10 dark:border-emerald-500/20';
        case 'Expired':
            return 'bg-destructive/15 text-destructive border-destructive/25 dark:bg-destructive/10 dark:border-destructive/20';
        case 'Low Balance':
            return 'bg-amber-500/15 text-amber-500 border-amber-500/25 dark:bg-amber-500/10 dark:border-amber-500/20';
        default:
            return 'bg-muted text-muted-foreground border-input';
    }
}

// Custom Delete Modal State
const isDeleteOpen = ref(false);
const cardToDelete = ref<number | null>(null);

function confirmDelete(id: number) {
    cardToDelete.value = id;
    isDeleteOpen.value = true;
}

function executeDelete() {
    if (cardToDelete.value !== null) {
        router.delete(`/cards/${cardToDelete.value}`, {
            onSuccess: () => {
                isDeleteOpen.value = false;
                cardToDelete.value = null;
            }
        });
    }
}

function cancelDelete() {
    isDeleteOpen.value = false;
    cardToDelete.value = null;
}

// Toast Notification State
const page = usePage();
const toastMessage = ref<{type: string, message: string} | null>(null);
let toastTimeout: ReturnType<typeof setTimeout>;

function showToast(toast: {type: string, message: string}) {
    toastMessage.value = toast;
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        toastMessage.value = null;
    }, 3000);
}

function dismissToast() {
    toastMessage.value = null;
    clearTimeout(toastTimeout);
}

watch(() => page.props.toast, (newToast) => {
    if (newToast) {
        showToast(newToast as {type: string, message: string});
    }
}, { deep: true });

onMounted(() => {
    if (page.props.toast) {
        showToast(page.props.toast as {type: string, message: string});
    }
});
</script>

<template>
    <Head title="Gift Cards" />

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <Heading title="Gift Cards" description="Manage your gift cards inventory" />
            <div class="flex items-center gap-3 flex-wrap">
                <Input
                    v-model="search"
                    placeholder="Search by card number..."
                    class="w-64"
                />
                <select
                    v-model="filter"
                    class="flex h-10 items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:w-[200px]"
                >
                    <option value="all">All Cards</option>
                    <option value="expiring_soon">Expiring Soon (30 Days)</option>
                    <option value="expired">Expired</option>
                </select>
                <a href="/cards/export">
                    <Button variant="outline">Export CSV</Button>
                </a>
                <Link href="/cards/create">
                    <Button>+ New Card</Button>
                </Link>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-sidebar-border/70 bg-muted/50 dark:border-sidebar-border">
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-muted-foreground">Card Number</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-muted-foreground">Activation Date</th>
                            <th 
                                class="whitespace-nowrap px-4 py-3 text-left font-medium text-muted-foreground cursor-pointer hover:text-foreground transition-colors group select-none"
                                @click="toggleSort('expiration_date')"
                            >
                                <div class="flex items-center gap-1">
                                    Expiration Date
                                    <span v-if="sortBy === 'expiration_date'" class="text-foreground">
                                        <svg v-if="sortOrder === 'asc'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                    </span>
                                    <span v-else class="text-muted-foreground/30 group-hover:text-muted-foreground/60 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    </span>
                                </div>
                            </th>
                            <th 
                                class="whitespace-nowrap px-4 py-3 text-left font-medium text-muted-foreground cursor-pointer hover:text-foreground transition-colors group select-none"
                                @click="toggleSort('status')"
                            >
                                <div class="flex items-center gap-1">
                                    Status
                                    <span v-if="sortBy === 'status'" class="text-foreground">
                                        <svg v-if="sortOrder === 'asc'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                    </span>
                                    <span v-else class="text-muted-foreground/30 group-hover:text-muted-foreground/60 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    </span>
                                </div>
                            </th>
                            <th 
                                class="whitespace-nowrap px-4 py-3 text-right font-medium text-muted-foreground cursor-pointer hover:text-foreground transition-colors group select-none"
                                @click="toggleSort('balance')"
                            >
                                <div class="flex items-center justify-end gap-1">
                                    Balance
                                    <span v-if="sortBy === 'balance'" class="text-foreground">
                                        <svg v-if="sortOrder === 'asc'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                    </span>
                                    <span v-else class="text-muted-foreground/30 group-hover:text-muted-foreground/60 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    </span>
                                </div>
                            </th>
                            <th class="whitespace-nowrap px-4 py-3 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="cards.data.length === 0">
                            <td colspan="6" class="px-4 py-12 text-center text-muted-foreground">
                                No gift cards found.
                            </td>
                        </tr>
                        <tr
                            v-for="card in cards.data"
                            :key="card.id"
                            class="border-b border-sidebar-border/70 transition-colors hover:bg-muted/30 dark:border-sidebar-border"
                        >
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-sm">{{ card.card_number }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ card.activation_date?.slice(0, 16).replace('T', ' ') }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ card.expiration_date?.slice(0, 10) }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border" :class="statusClass(card.status)">
                                    {{ card.status }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-mono font-semibold">
                                ${{ parseFloat(card.balance).toFixed(2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="`/cards/${card.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button variant="destructive" size="sm" @click="confirmDelete(card.id)">
                                        Delete
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="cards.last_page > 1"
                class="flex items-center justify-between border-t border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border"
            >
                <p class="text-sm text-muted-foreground">
                    Page {{ cards.current_page }} of {{ cards.last_page }} ({{ cards.total }} total)
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in cards.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-sm transition-colors"
                            :class="link.active
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'border-sidebar-border/70 hover:bg-muted/50 dark:border-sidebar-border'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-sm text-muted-foreground opacity-50"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <div v-if="isDeleteOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/85 backdrop-blur-sm animate-in fade-in duration-200">
        <div class="w-full max-w-md bg-card border border-sidebar-border rounded-xl shadow-xl p-6 space-y-4 animate-in zoom-in-95 duration-200">
            <h3 class="text-lg font-semibold text-foreground">Confirm Deletion</h3>
            <p class="text-sm text-muted-foreground">
                Are you sure you want to permanently delete this gift card? This action cannot be undone and will be permanently logged.
            </p>
            <div class="flex justify-end gap-3 pt-2">
                <Button variant="outline" @click="cancelDelete">Cancel</Button>
                <Button variant="destructive" @click="executeDelete">Delete Card</Button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="transform translate-x-full opacity-0"
        enter-to-class="transform translate-x-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="transform translate-x-0 opacity-100"
        leave-to-class="transform translate-x-full opacity-0"
    >
        <div v-if="toastMessage" class="fixed top-24 right-4 z-[100] max-w-sm w-full bg-card border border-emerald-500/30 shadow-lg rounded-xl pointer-events-auto flex ring-1 ring-black/5 dark:ring-white/5">
            <div class="flex-1 w-0 p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0 pt-0.5">
                        <div class="h-8 w-8 rounded-full bg-emerald-500/20 flex items-center justify-center">
                            <svg class="h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 w-0 flex-1">
                        <p class="text-sm font-bold text-emerald-500 uppercase tracking-widest mt-1">
                            {{ toastMessage.type === 'success' ? 'Success!' : 'Notice' }}
                        </p>
                        <p class="mt-1 text-sm text-foreground font-medium leading-relaxed">
                            {{ toastMessage.message }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex border-l border-sidebar-border/50">
                <button @click="dismissToast" type="button" class="w-full border border-transparent rounded-none rounded-r-xl p-4 flex items-center justify-center text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-muted/30 focus:outline-none transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
        </div>
    </Transition>
</template>

