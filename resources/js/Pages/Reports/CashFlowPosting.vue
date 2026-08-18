<script setup>
import { computed, ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import Pagination from '../../Components/Pagination.vue';
import { money, dateTime } from '../../utils/format';

const props = defineProps({
    orders: Object,
    filters: Object,
});

const filterForm = useForm({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
});

const applyFilter = () => {
    filterForm.get(route('reports.cash-flow.posting'), { preserveState: true });
};

const selectedIds = ref([]);
const postingForm = useForm({
    order_ids: selectedIds,
    posting_date: new Date().toISOString().slice(0, 10),
});

const toggle = (id) => {
    const idx = selectedIds.value.indexOf(id);
    if (idx === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(idx, 1);
    }
};

const toggleAll = () => {
    const currentPageIds = props.orders.data.map((o) => o.id);
    const allSelected = currentPageIds.every((id) => selectedIds.value.includes(id));
    if (allSelected) {
        selectedIds.value = selectedIds.value.filter((id) => !currentPageIds.includes(id));
    } else {
        currentPageIds.forEach((id) => {
            if (!selectedIds.value.includes(id)) selectedIds.value.push(id);
        });
    }
};

const allSelected = computed(() => {
    const ids = props.orders.data.map((o) => o.id);
    return ids.length > 0 && ids.every((id) => selectedIds.value.includes(id));
});

const totalSelectedAmount = computed(() => {
    return props.orders.data
        .filter((o) => selectedIds.value.includes(o.id))
        .reduce((sum, o) => sum + Number(o.total), 0);
});

const submitPosting = () => {
    if (!selectedIds.value.length) return;
    postingForm.order_ids = selectedIds.value;
    postingForm.post(route('reports.cash-flow.post-transaction'), {
        preserveScroll: true,
        onSuccess: () => (selectedIds.value = []),
    });
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div>
        <PageHeader title="Posting Transaksi ke Arus Kas" subtitle="Kirim transaksi penjualan selesai ke buku arus kas">
            <Link :href="route('reports.cash-flow')" class="btn-secondary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5M3.75 15h16.5" /></svg>
                Arus Kas
            </Link>
        </PageHeader>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="card mb-4 p-4">
                    <form class="flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="applyFilter">
                        <div class="sm:w-48">
                            <label class="label">Dari Tanggal</label>
                            <input v-model="filterForm.start_date" type="date" class="input">
                        </div>
                        <div class="sm:w-48">
                            <label class="label">Sampai Tanggal</label>
                            <input v-model="filterForm.end_date" type="date" class="input">
                        </div>
                        <button type="submit" class="btn-primary">Terapkan</button>
                    </form>
                </div>

                <div class="card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[680px] text-left">
                            <thead class="border-b border-slate-200 bg-slate-50">
                                <tr>
                                    <th class="table-th w-12">
                                        <input type="checkbox" class="h-4 w-4 rounded accent-indigo-600" :checked="allSelected" @change="toggleAll">
                                    </th>
                                    <th class="table-th">Order #</th>
                                    <th class="table-th">Tanggal</th>
                                    <th class="table-th">Tipe</th>
                                    <th class="table-th text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="order in orders.data" :key="order.id" class="transition hover:bg-slate-50" :class="selectedIds.includes(order.id) ? 'bg-indigo-50/50' : ''">
                                    <td class="table-td">
                                        <input
                                            type="checkbox"
                                            class="h-4 w-4 rounded accent-indigo-600"
                                            :checked="selectedIds.includes(order.id)"
                                            @change="toggle(order.id)"
                                        >
                                    </td>
                                    <td class="table-td font-semibold text-slate-800">#{{ order.order_number }}</td>
                                    <td class="table-td whitespace-nowrap text-slate-500">{{ dateTime(order.created_at) }}</td>
                                    <td class="table-td capitalize">{{ order.order_type }}</td>
                                    <td class="table-td text-right font-bold text-slate-800">{{ money(order.total) }}</td>
                                </tr>
                                <tr v-if="!orders.data.length">
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <p class="text-4xl">✅</p>
                                        <p class="mt-2 font-medium text-slate-500">Tidak ada transaksi selesai yang belum diposting</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="orders.links" :from="orders.from" :to="orders.to" :total="orders.total" />
                </div>
            </div>

            <div class="card h-fit p-5">
                <h3 class="mb-4 font-bold text-slate-900">Ringkasan Posting</h3>
                <div class="rounded-xl bg-slate-50 p-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Transaksi dipilih</span>
                        <span class="font-bold text-slate-800">{{ selectedIds.length }}</span>
                    </div>
                    <div class="mt-1 flex justify-between text-sm">
                        <span class="text-slate-500">Total nilai</span>
                        <span class="font-bold text-emerald-600">{{ money(totalSelectedAmount) }}</span>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="label">Tanggal Posting</label>
                    <input v-model="postingForm.posting_date" type="date" class="input">
                </div>
                <p v-if="postingForm.errors.order_ids || postingForm.errors.posting_date" class="mt-2 text-xs text-rose-600">
                    {{ postingForm.errors.order_ids || postingForm.errors.posting_date }}
                </p>

                <button
                    type="button"
                    class="btn-primary mt-4 w-full"
                    :disabled="selectedIds.length === 0 || postingForm.processing"
                    @click="submitPosting"
                >
                    <svg v-if="postingForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Posting {{ selectedIds.length }} Transaksi
                </button>
            </div>
        </div>
    </div>
</template>