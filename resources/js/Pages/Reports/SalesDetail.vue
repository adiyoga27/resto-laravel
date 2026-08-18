<script setup>
import { ref } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import StatusBadge from '../../Components/StatusBadge.vue';
import Modal from '../../Components/Modal.vue';
import { money, dateTime } from '../../utils/format';

const props = defineProps({
    order: Object,
});

const page = usePage();
const isAdmin = page.props.auth.user?.is_admin;

const statusLabel = { baru: 'Baru', diproses: 'Diproses', siap: 'Siap', selesai: 'Selesai', dibatalkan: 'Dibatalkan' };
const typeLabel = { 'dine-in': 'Dine In', delivery: 'Delivery', pickup: 'Pickup' };
const paymentMethodLabel = { cash: 'Cash', qris: 'QRIS', transfer: 'Transfer', card: 'Card' };
const paymentStatusLabel = { pending: 'Pending', paid: 'Paid', failed: 'Failed', refunded: 'Refunded' };

const editForm = useForm({
    order_status: props.order.order_status,
    customer_name: props.order.customer_name ?? '',
    customer_phone: props.order.customer_phone ?? '',
    discount: Number(props.order.discount ?? 0),
    notes: props.order.notes ?? '',
});

const showEdit = ref(false);

const saveEdit = () => {
    editForm.put(route('reports.sales.update', props.order.id), {
        preserveScroll: true,
        onSuccess: () => (showEdit.value = false),
    });
};

const deleting = ref(false);
const busy = ref(false);

const confirmDelete = () => {
    busy.value = true;
    router.delete(route('reports.sales.destroy', props.order.id), {
        onFinish: () => (busy.value = false),
    });
};

const infoRows = computedInfo();

function computedInfo() {
    const o = props.order;
    return [
        { label: 'Order #', value: `#${o.order_number}` },
        { label: 'Tanggal', value: dateTime(o.created_at) },
        { label: 'Status', value: statusLabel[o.order_status] },
        { label: 'Tipe', value: typeLabel[o.order_type] ?? o.order_type },
        { label: 'Channel', value: o.channel },
        { label: 'Meja', value: o.restaurant_table?.table_number ?? '-' },
        { label: 'Kasir', value: o.created_by?.name ?? '-' },
        { label: 'Pelanggan', value: o.customer_name ?? '-' },
        { label: 'Telp', value: o.customer_phone ?? '-' },
    ];
}

defineOptions({ layout: AppLayout });
</script>

<template>
    <div>
        <PageHeader :title="`Detail Transaksi #${order.order_number}`" subtitle="Rincian pesanan dan pembayaran">
            <Link :href="route('reports.sales')" class="btn-secondary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Kembali
            </Link>
        </PageHeader>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Items -->
            <div class="space-y-6 lg:col-span-2">
                <div class="card overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="font-bold text-slate-900">Item Pesanan</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[560px] text-left">
                            <thead class="border-b border-slate-200 bg-slate-50">
                                <tr>
                                    <th class="table-th">Menu</th>
                                    <th class="table-th">Kategori</th>
                                    <th class="table-th text-center">Qty</th>
                                    <th class="table-th text-right">Harga</th>
                                    <th class="table-th text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in order.order_items" :key="item.id">
                                    <td class="table-td font-medium text-slate-800">{{ item.menu_item?.name ?? '-' }}</td>
                                    <td class="table-td text-slate-400">{{ item.menu_item?.category?.name ?? '-' }}</td>
                                    <td class="table-td text-center">{{ item.quantity }}</td>
                                    <td class="table-td text-right">{{ money(item.price) }}</td>
                                    <td class="table-td text-right font-semibold">{{ money(item.subtotal) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="border-t border-slate-200 bg-slate-50">
                                <tr>
                                    <th colspan="4" class="px-6 py-3 text-right text-sm text-slate-500">Subtotal</th>
                                    <th class="px-6 py-3 text-right text-sm font-semibold">{{ money(order.subtotal) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="px-6 py-3 text-right text-sm text-rose-500">Diskon</th>
                                    <th class="px-6 py-3 text-right text-sm font-semibold text-rose-500">-{{ money(order.discount) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="px-6 py-3 text-right text-sm text-slate-500">Tax (11%)</th>
                                    <th class="px-6 py-3 text-right text-sm font-semibold">{{ money(order.tax) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="px-6 py-3 text-right text-sm font-bold text-slate-900">Total</th>
                                    <th class="px-6 py-3 text-right text-base font-extrabold text-indigo-600">{{ money(order.total) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div v-if="order.payments.length" class="card overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="font-bold text-slate-900">Pembayaran</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[480px] text-left">
                            <thead class="border-b border-slate-200 bg-slate-50">
                                <tr>
                                    <th class="table-th">Metode</th>
                                    <th class="table-th">Referensi</th>
                                    <th class="table-th text-right">Jumlah</th>
                                    <th class="table-th">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="payment in order.payments" :key="payment.id">
                                    <td class="table-td">
                                        <span class="badge bg-slate-100 text-slate-700 capitalize">{{ paymentMethodLabel[payment.method] ?? payment.method }}</span>
                                    </td>
                                    <td class="table-td text-slate-400">{{ payment.reference || '-' }}</td>
                                    <td class="table-td text-right font-semibold">{{ money(payment.amount) }}</td>
                                    <td class="table-td capitalize">{{ paymentStatusLabel[payment.status] ?? payment.status }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="card h-fit p-6">
                <h3 class="mb-4 font-bold text-slate-900">Info Transaksi</h3>
                <dl class="space-y-3">
                    <div v-for="row in infoRows" :key="row.label" class="flex items-start justify-between gap-3">
                        <dt class="text-sm text-slate-500">{{ row.label }}</dt>
                        <dd v-if="row.label === 'Status'" class="text-sm">
                            <StatusBadge :value="order.order_status">{{ row.value }}</StatusBadge>
                        </dd>
                        <dd v-else class="text-right text-sm font-semibold text-slate-800">{{ row.value }}</dd>
                    </div>
                </dl>

                <template v-if="order.notes">
                    <hr class="my-4 border-slate-100">
                    <p class="mb-1 text-sm font-semibold text-slate-700">Catatan</p>
                    <p class="text-sm text-slate-500">{{ order.notes }}</p>
                </template>

                <div v-if="isAdmin" class="mt-6 flex gap-3">
                    <button class="btn-warning flex-1" @click="showEdit = true">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.4a2 2 0 112.8 2.8L11 17l-4 1 1-4 9.6-9.4z" /></svg>
                        Edit
                    </button>
                    <button class="btn-danger flex-1" @click="deleting = true">Hapus</button>
                </div>
            </div>
        </div>

        <!-- Edit modal -->
        <div v-if="showEdit" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showEdit = false" />
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="font-bold text-slate-900">Edit Transaksi #{{ order.order_number }}</h3>
                    <button class="text-slate-400 hover:text-slate-600" @click="showEdit = false">
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
                    <p v-if="Object.keys(editForm.errors).length" class="text-sm text-rose-600">{{ Object.values(editForm.errors)[0] }}</p>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button type="button" class="btn-secondary" @click="showEdit = false">Batal</button>
                    <button type="button" class="btn-primary" :disabled="editForm.processing" @click="saveEdit">Simpan</button>
                </div>
            </div>
        </div>

        <Modal
            :open="deleting"
            title="Hapus Transaksi"
            :message="`Yakin ingin menghapus order #${order.order_number}?`"
            :busy="busy"
            @close="deleting = false"
            @confirm="confirmDelete"
        />
    </div>
</template>