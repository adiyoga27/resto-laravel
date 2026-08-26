<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import Pagination from '../../../Components/Pagination.vue';
import StatusBadge from '../../../Components/StatusBadge.vue';
import { dateTime } from '../../../utils/format';

const props = defineProps({
    logs: Object,
    users: Array,
    filters: Object,
});

const filterForm = useForm({
    search: props.filters.search ?? '',
    user_id: props.filters.user_id ?? '',
    to_status: props.filters.to_status ?? '',
});

const applyFilter = () => {
    filterForm.get(route('admin.order-logs.index'), { preserveState: true });
};

const resetFilter = () => {
    filterForm.search = '';
    filterForm.user_id = '';
    filterForm.to_status = '';
    applyFilter();
};

const statuses = [
    { value: 'baru', label: 'Baru' },
    { value: 'diproses', label: 'Diproses' },
    { value: 'siap', label: 'Siap' },
    { value: 'selesai', label: 'Selesai' },
    { value: 'dibatalkan', label: 'Dibatalkan' },
];

const statusLabel = (value) => statuses.find((s) => s.value === value)?.label ?? value ?? '-';

const sourceLabel = (value) => ({
    pos: 'POS',
    kitchen: 'Dapur',
    report: 'Laporan',
    mobile: 'Mobile',
    system: 'Sistem',
}[value] ?? value ?? '-');

defineOptions({ layout: AppLayout });
</script>

<template>
    <div>
        <PageHeader title="Log Aktivitas Pesanan" subtitle="Riwayat perubahan status invoice" />

        <div class="card mb-4 p-4">
            <form class="flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="applyFilter">
                <div class="sm:flex-1">
                    <label class="label">Cari Order</label>
                    <input v-model="filterForm.search" type="text" class="input" placeholder="Cari nomor order...">
                </div>
                <div class="sm:w-56">
                    <label class="label">User</label>
                    <select v-model="filterForm.user_id" class="input">
                        <option value="">Semua User</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                </div>
                <div class="sm:w-44">
                    <label class="label">Status Tujuan</label>
                    <select v-model="filterForm.to_status" class="input">
                        <option value="">Semua</option>
                        <option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-secondary">Terapkan</button>
                    <button type="button" class="btn-ghost border border-slate-300" @click="resetFilter">Reset</button>
                </div>
            </form>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[780px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="table-th">Tanggal</th>
                            <th class="table-th">Order</th>
                            <th class="table-th">Perubahan Status</th>
                            <th class="table-th">Oleh</th>
                            <th class="table-th">Sumber</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="log in logs.data" :key="log.id" class="transition hover:bg-slate-50">
                            <td class="table-td whitespace-nowrap text-slate-500">{{ dateTime(log.created_at) }}</td>
                            <td class="table-td">
                                <Link :href="route('reports.sales.show', log.order?.id)" class="font-semibold text-indigo-600 hover:underline">
                                    #{{ log.order?.order_number ?? '—' }}
                                </Link>
                            </td>
                            <td class="table-td">
                                <div class="flex items-center gap-2">
                                    <StatusBadge :value="log.from_status ?? ''">{{ statusLabel(log.from_status) }}</StatusBadge>
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                    <StatusBadge :value="log.to_status">{{ statusLabel(log.to_status) }}</StatusBadge>
                                </div>
                            </td>
                            <td class="table-td text-slate-600">{{ log.user?.name ?? 'Sistem' }}</td>
                            <td class="table-td">
                                <span class="badge bg-slate-100 text-slate-700">{{ sourceLabel(log.source) }}</span>
                            </td>
                        </tr>
                        <tr v-if="!logs.data.length">
                            <td colspan="5" class="px-6 py-16 text-center">
                                <p class="text-4xl">📜</p>
                                <p class="mt-2 font-medium text-slate-500">Belum ada log aktivitas</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="logs.links" :from="logs.from" :to="logs.to" :total="logs.total" />
        </div>
    </div>
</template>