<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import StatusBadge from '../../Components/StatusBadge.vue';

const props = defineProps({
    tableUsage: Array,
    filters: Object,
});

const filterForm = useForm({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
});

const applyFilter = () => {
    filterForm.get(route('reports.tables'), { preserveState: true });
};

const statusLabel = { kosong: 'Kosong', terisi: 'Terisi', direservasi: 'Direservasi' };

const maxUsage = Math.max(...props.tableUsage.map((t) => t.orders_count ?? 0), 1);

defineOptions({ layout: AppLayout });
</script>

<template>
    <div>
        <PageHeader title="Laporan Meja" subtitle="Rekap penggunaan meja">
            <a
                :href="route('reports.tables.export', { start_date: filterForm.start_date, end_date: filterForm.end_date })"
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

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="table-th">Meja</th>
                            <th class="table-th">Kapasitas</th>
                            <th class="table-th">Status</th>
                            <th class="table-th text-right">Total Order</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="table in tableUsage" :key="table.id" class="transition hover:bg-slate-50">
                            <td class="table-td font-semibold text-slate-800">Meja {{ table.table_number }}</td>
                            <td class="table-td text-slate-400">{{ table.capacity }} kursi</td>
                            <td class="table-td">
                                <StatusBadge :value="table.status">{{ statusLabel[table.status] }}</StatusBadge>
                            </td>
                            <td class="table-td">
                                <div class="flex items-center justify-end gap-3">
                                    <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100">
                                        <div
                                            class="h-full rounded-full transition-all"
                                            :class="table.orders_count > 0 ? 'bg-gradient-to-r from-indigo-500 to-indigo-600' : 'bg-slate-200'"
                                            :style="{ width: Math.max(4, (Number(table.orders_count ?? 0) / maxUsage) * 100) + '%' }"
                                        />
                                    </div>
                                    <span class="w-10 text-right font-semibold text-slate-800">{{ table.orders_count ?? 0 }}x</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!tableUsage.length">
                            <td colspan="4" class="px-6 py-16 text-center">
                                <p class="text-4xl">🪑</p>
                                <p class="mt-2 font-medium text-slate-500">Belum ada meja</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>