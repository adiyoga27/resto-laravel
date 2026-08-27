<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import Pagination from '../../../Components/Pagination.vue';
import { dateTime } from '../../../utils/format';

const props = defineProps({
    logs: Object,
    users: Array,
    actionGroups: Object,
    filters: Object,
});

const filterForm = useForm({
    search: props.filters.search ?? '',
    user_id: props.filters.user_id ?? '',
    action: props.filters.action ?? '',
    start_date: props.filters.start_date ?? '',
    end_date: props.filters.end_date ?? '',
});

const applyFilter = () => {
    filterForm.get(route('admin.activity-logs.index'), { preserveState: true });
};

const resetFilter = () => {
    filterForm.clearErrors();
    Object.keys(filterForm.data()).forEach((key) => {
        filterForm[key] = '';
    });
    applyFilter();
};

const actionLabels = {
    'auth.login': 'Login',
    'auth.logout': 'Logout',
    'order.checkout': 'Checkout',
    'order.created': 'Buat Order',
    'order.updated': 'Update Order',
    'order.completed': 'Selesaikan Order',
    'order.cancelled': 'Batalkan Order',
    'order.status_updated': 'Ubah Status Order',
    'sales.updated': 'Update Penjualan',
    'sales.deleted': 'Hapus Penjualan',
    'user.created': 'Buat User',
    'user.updated': 'Update User',
    'user.deleted': 'Hapus User',
    'menu_category.created': 'Tambah Kategori',
    'menu_category.updated': 'Update Kategori',
    'menu_category.deleted': 'Hapus Kategori',
    'menu_item.created': 'Tambah Menu',
    'menu_item.updated': 'Update Menu',
    'menu_item.deleted': 'Hapus Menu',
    'table.created': 'Tambah Meja',
    'table.updated': 'Update Meja',
    'table.deleted': 'Hapus Meja',
    'additional_cost.created': 'Tambah Biaya',
    'additional_cost.updated': 'Update Biaya',
    'additional_cost.deleted': 'Hapus Biaya',
    'store_profile.created': 'Simpan Data Toko',
    'store_profile.updated': 'Update Data Toko',
    'ingredient.created': 'Tambah Bahan',
    'ingredient.updated': 'Update Bahan',
    'ingredient.deleted': 'Hapus Bahan',
    'recipe.created': 'Tambah Resep',
    'recipe.updated': 'Update Resep',
    'recipe.deleted': 'Hapus Resep',
    'stock_log.created': 'Mutasi Stok',
    'stock_log.production': 'Produksi Menu',
    'stock_opname.created': 'Buat Opname',
    'stock_opname.posted': 'Posting Opname',
    'stock_opname.deleted': 'Hapus Opname',
    'cash_flow.created': 'Tambah Arus Kas',
    'cash_flow.updated': 'Update Arus Kas',
    'cash_flow.deleted': 'Hapus Arus Kas',
    'cash_flow.posted': 'Posting Arus Kas',
    'cash_flow.unposted': 'Batal Posting Arus Kas',
    'cash_flow.posted_transactions': 'Posting Transaksi',
    'reservation.created': 'Buat Reservasi',
    'profile.updated': 'Update Profil',
    'profile.password_updated': 'Ganti Password',
};

const actionLabel = (action) => actionLabels[action] ?? action ?? '-';

const flatGroups = computed(() => {
    const groups = [];
    Object.entries(props.actionGroups ?? {}).forEach(([key, group]) => {
        groups.push({ label: group.label, options: group.items.map((item) => ({ value: item, label: actionLabels[item] ?? item })) });
    });
    return groups;
});

const badgeColor = (action) => {
    if (action?.startsWith('auth.')) return 'bg-sky-100 text-sky-700';
    if (action?.startsWith('order.') || action?.startsWith('sales.')) return 'bg-indigo-100 text-indigo-700';
    if (action?.startsWith('user.') || action?.startsWith('profile.')) return 'bg-violet-100 text-violet-700';
    if (action?.startsWith('stock.') || action?.startsWith('ingredient.') || action?.startsWith('recipe.')) return 'bg-emerald-100 text-emerald-700';
    if (action?.startsWith('cash_flow.')) return 'bg-amber-100 text-amber-700';
    return 'bg-slate-100 text-slate-700';
};

const shortDevice = (ua) => {
    if (!ua) return '-';
    if (/Android/i.test(ua)) return 'Android';
    if (/iPhone|iPad|iPod/i.test(ua)) return 'iOS';
    if (/Windows/i.test(ua)) return 'Windows';
    if (/Macintosh|Mac OS/i.test(ua)) return 'macOS';
    if (/Linux/i.test(ua)) return 'Linux';
    return ua.split(' ').slice(0, 3).join(' ');
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div>
        <PageHeader title="Log Aktivitas User" subtitle="Rekam semua aktivitas yang dilakukan oleh user" />

        <div class="card mb-4 p-4">
            <form class="flex flex-col gap-3 xl:flex-row xl:items-end" @submit.prevent="applyFilter">
                <div class="xl:flex-1">
                    <label class="label">Cari</label>
                    <input v-model="filterForm.search" type="text" class="input" placeholder="Cari deskripsi atau aksi...">
                </div>
                <div class="xl:w-48">
                    <label class="label">User</label>
                    <select v-model="filterForm.user_id" class="input">
                        <option value="">Semua User</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                </div>
                <div class="xl:w-60">
                    <label class="label">Aksi</label>
                    <select v-model="filterForm.action" class="input">
                        <option value="">Semua Aksi</option>
                        <optgroup v-for="group in flatGroups" :key="group.label" :label="group.label">
                            <option v-for="option in group.options" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </optgroup>
                    </select>
                </div>
                <div>
                    <label class="label">Dari Tanggal</label>
                    <input v-model="filterForm.start_date" type="date" class="input">
                </div>
                <div>
                    <label class="label">Sampai Tanggal</label>
                    <input v-model="filterForm.end_date" type="date" class="input">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-secondary">Terapkan</button>
                    <button type="button" class="btn-ghost border border-slate-300" @click="resetFilter">Reset</button>
                    <Link :href="route('admin.activity-logs.export', filterForm.data())" class="btn-ghost border border-slate-300">
                        <svg class="mr-1 inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" /></svg>
                        Export
                    </Link>
                </div>
            </form>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="table-th">Tanggal</th>
                            <th class="table-th">User</th>
                            <th class="table-th">Aksi</th>
                            <th class="table-th">Deskripsi</th>
                            <th class="table-th">IP &amp; Perangkat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="log in logs.data" :key="log.id" class="transition hover:bg-slate-50">
                            <td class="table-td whitespace-nowrap text-slate-500">{{ dateTime(log.created_at) }}</td>
                            <td class="table-td">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 text-[10px] font-bold text-white">
                                        {{ (log.user?.name?.charAt(0) ?? 'S')?.toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-700">{{ log.user?.name ?? 'Sistem' }}</p>
                                        <p class="text-[11px] text-slate-400">{{ log.user?.email ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td">
                                <span class="badge" :class="badgeColor(log.action)">{{ actionLabel(log.action) }}</span>
                            </td>
                            <td class="table-td text-slate-600">{{ log.description ?? '-' }}</td>
                            <td class="table-td whitespace-nowrap">
                                <p class="text-xs text-slate-500">{{ log.ip_address ?? '-' }}</p>
                                <p class="text-[11px] text-slate-400">{{ shortDevice(log.user_agent) }}</p>
                            </td>
                        </tr>
                        <tr v-if="!logs.data.length">
                            <td colspan="5" class="px-6 py-16 text-center">
                                <p class="text-4xl">🕐</p>
                                <p class="mt-2 font-medium text-slate-500">Belum ada aktivitas yang tercatat</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="logs.links" :from="logs.from" :to="logs.to" :total="logs.total" />
        </div>
    </div>
</template>