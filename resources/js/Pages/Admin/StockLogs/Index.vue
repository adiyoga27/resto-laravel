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
    filters: Object,
});

const filterForm = useForm({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
});

const applyFilter = () => {
    filterForm.get(route('admin.stock-logs.index'), { preserveState: true });
};

const typeLabel = {
    in: 'Masuk',
    out: 'Keluar',
    production: 'Produksi',
    adjustment: 'Penyesuaian',
    opname: 'Opname',
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div>
        <PageHeader title="Mutasi Stok" subtitle="Riwayat perubahan stok bahan baku">
            <Link :href="route('admin.stock-logs.create')" class="btn-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4" /></svg>
                Mutasi Baru
            </Link>
            <Link :href="route('admin.stock-logs.create-production')" class="btn-warning">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z" /></svg>
                Produksi
            </Link>
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
                <button type="submit" class="btn-secondary">Terapkan</button>
            </form>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="table-th">Tanggal</th>
                            <th class="table-th">Bahan</th>
                            <th class="table-th">Tipe</th>
                            <th class="table-th text-right">Jumlah</th>
                            <th class="table-th text-right">Stok Akhir</th>
                            <th class="table-th">Referensi</th>
                            <th class="table-th">Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="log in logs.data" :key="log.id" class="transition hover:bg-slate-50">
                            <td class="table-td whitespace-nowrap text-slate-500">{{ dateTime(log.created_at) }}</td>
                            <td class="table-td font-medium text-slate-800">{{ log.ingredient?.name ?? '—' }}</td>
                            <td class="table-td">
                                <StatusBadge :value="log.type">{{ typeLabel[log.type] }}</StatusBadge>
                            </td>
                            <td class="table-td text-right font-semibold" :class="log.type === 'in' || log.type === 'adjustment' ? 'text-emerald-600' : 'text-rose-600'">
                                {{ Number(log.quantity).toLocaleString('id-ID') }}
                            </td>
                            <td class="table-td text-right text-slate-500">{{ Number(log.stock_after).toLocaleString('id-ID') }}</td>
                            <td class="table-td max-w-[220px] truncate text-slate-400">{{ log.reference || log.notes || '—' }}</td>
                            <td class="table-td text-slate-400">{{ log.user?.name ?? '—' }}</td>
                        </tr>
                        <tr v-if="!logs.data.length">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <p class="text-4xl">📦</p>
                                <p class="mt-2 font-medium text-slate-500">Belum ada mutasi stok</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="logs.links" :from="logs.from" :to="logs.to" :total="logs.total" />
        </div>
    </div>
</template>