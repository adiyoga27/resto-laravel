<script setup>
import { ref } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import Pagination from '../../Components/Pagination.vue';
import StatusBadge from '../../Components/StatusBadge.vue';
import Modal from '../../Components/Modal.vue';
import { money, dateTime } from '../../utils/format';

const props = defineProps({
    orders: Object,
    totalRevenue: Number,
    totalOrders: Number,
    filters: Object,
});

const page = usePage();
const isAdmin = page.props.auth.user?.is_admin;

const filterForm = useForm({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
});

const applyFilter = () => {
    filterForm.get(route('reports.sales'), { preserveState: true });
};

const editTarget = ref(null);
const editForm = useForm({
    order_status: '',
    customer_name: '',
    customer_phone: '',
    discount: 0,
    notes: '',
});

const openEdit = (order) => {
    editTarget.value = order;
    editForm.clearErrors();
    editForm.order_status = order.order_status;
    editForm.customer_name = order.customer_name ?? '';
    editForm.customer_phone = order.customer_phone ?? '';
    editForm.discount = Number(order.discount ?? 0);
    editForm.notes = order.notes ?? '';
};

const closeEdit = () => {
    editTarget.value = null;
};

const saveEdit = () => {
    editForm.put(route('reports.sales.update', editTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            editTarget.value = null;
        },
    });
};

const deleting = ref(null);
const busy = ref(false);

const confirmDelete = () => {
    busy.value = true;
    router.delete(route('reports.sales.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
            busy.value = false;
        },
        onError: () => (busy.value = false),
    });
};

const typeLabel = { 'dine-in': 'Dine In', delivery: 'Delivery', pickup: 'Pickup' };
const statusLabel = { baru: 'Baru', diproses: 'Diproses', siap: 'Siap', selesai: 'Selesai', dibatalkan: 'Dibatalkan' };

defineOptions({ layout: AppLayout });
</script>

<template>
    <div>
        <PageHeader title="Laporan Penjualan" subtitle="Rekap transaksi penjualan">
            <a
                :href="route('reports.sales.export', { start_date: filterForm.start_date, end_date: filterForm.end_date })"
                class="btn-success"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Export Excel
            </a>
        </PageHeader>

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

        <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="card flex items-center gap-4 p-5">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Total Orders</p>
                    <p class="text-xl font-extrabold text-slate-900">{{ totalOrders }}</p>
                </div>
            </div>
            <div class="card flex items-center gap-4 p-5">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Revenue</p>
                    <p class="text-xl font-extrabold text-emerald-600">{{ money(totalRevenue) }}</p>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="table-th">Order #</th>
                            <th class="table-th">Tanggal</th>
                            <th class="table-th">Tipe</th>
                            <th class="table-th">Channel</th>
                            <th class="table-th">Status</th>
                            <th class="table-th text-right">Total</th>
                            <th v-if="isAdmin" class="table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="order in orders.data" :key="order.id" class="transition hover:bg-slate-50">
                            <td class="table-td font-semibold text-slate-800">#{{ order.order_number }}</td>
                            <td class="table-td whitespace-nowrap text-slate-500">{{ dateTime(order.created_at) }}</td>
                            <td class="table-td">{{ typeLabel[order.order_type] ?? order.order_type }}</td>
                            <td class="table-td">
                                <span class="badge bg-slate-800 text-white capitalize">{{ order.channel }}</span>
                            </td>
                            <td class="table-td">
                                <StatusBadge :value="order.order_status">{{ statusLabel[order.order_status] }}</StatusBadge>
                            </td>
                            <td class="table-td text-right font-bold text-slate-800">{{ money(order.total) }}</td>
                            <td v-if="isAdmin" class="table-td">
                                <div class="flex justify-end gap-1.5">
                                    <Link :href="route('reports.sales.show', order.id)" class="btn-ghost px-2.5 py-1.5 text-xs" title="Detail">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </Link>
                                    <button class="btn-ghost px-2.5 py-1.5 text-xs" title="Edit" @click="openEdit(order)">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.4a2 2 0 112.8 2.8L11 17l-4 1 1-4 9.6-9.4z" /></svg>
                                    </button>
                                    <button class="btn-ghost px-2.5 py-1.5 text-xs text-rose-600 hover:bg-rose-50" title="Hapus" @click="deleting = order">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!orders.data.length">
                            <td :colspan="isAdmin ? 7 : 6" class="px-6 py-16 text-center">
                                <p class="text-4xl">📊</p>
                                <p class="mt-2 font-medium text-slate-500">Belum ada transaksi pada rentang tanggal ini</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="orders.links" :from="orders.from" :to="orders.to" :total="orders.total" />
        </div>

        <!-- Edit modal -->
        <div v-if="editTarget" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeEdit" />
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="font-bold text-slate-900">Edit Transaksi #{{ editTarget.order_number }}</h3>
                    <button class="text-slate-400 hover:text-slate-600" @click="closeEdit">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="space-y-4 p-6">
                    <div>
                        <label class="label">Status</label>
                        <select v-model="editForm.order_status" class="input">
                            <option value="baru">Baru</option>
                            <option value="diproses">Diproses</option>
                            <option value="siap">Siap</option>
                            <option value="selesai">Selesai</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">Nama Pelanggan</label>
                            <input v-model="editForm.customer_name" type="text" class="input">
                        </div>
                        <div>
                            <label class="label">No. Telepon</label>
                            <input v-model="editForm.customer_phone" type="text" class="input">
                        </div>
                    </div>
                    <div>
                        <label class="label">Diskon (Rp)</label>
                        <input v-model.number="editForm.discount" type="number" min="0" class="input">
                    </div>
                    <div>
                        <label class="label">Catatan</label>
                        <textarea v-model="editForm.notes" rows="2" class="input" />
                    </div>
                    <p v-if="editForm.errors.order_status || editForm.errors.customer_name || editForm.errors.notes" class="text-sm text-rose-600">
                        {{ editForm.errors.order_status || editForm.errors.customer_name || editForm.errors.notes }}
                    </p>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button type="button" class="btn-secondary" @click="closeEdit">Batal</button>
                    <button type="button" class="btn-primary" :disabled="editForm.processing" @click="saveEdit">Simpan</button>
                </div>
            </div>
        </div>

        <Modal
            :open="!!deleting"
            title="Hapus Transaksi"
            :message="`Yakin ingin menghapus order #${deleting?.order_number}?`"
            :busy="busy"
            @close="deleting = null"
            @confirm="confirmDelete"
        />
    </div>
</template>