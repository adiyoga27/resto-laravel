<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    popularItems: Object,
    filters: Object,
});

const filterForm = useForm({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
});

const applyFilter = () => {
    filterForm.get(route('reports.popular-menu'), { preserveState: true });
};

const maxCount = Math.max(...props.popularItems.data.map((item) => item.total_orders ?? 0), 1);
const emojis = ['🥇', '🥈', '🥉'];

const rank = (index) => emojis[index] ?? `${index + 1}.`;

defineOptions({ layout: AppLayout });
</script>

<template>
    <div>
        <PageHeader title="Menu Terlaris" subtitle="Menu paling banyak dipesan">
            <a
                :href="route('reports.popular-menu.export', { start_date: filterForm.start_date, end_date: filterForm.end_date })"
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
                            <th class="table-th w-16">Peringkat</th>
                            <th class="table-th">Menu</th>
                            <th class="table-th">Kategori</th>
                            <th class="table-th text-right">Total Order</th>
                            <th class="table-th text-right">Qty Terjual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(item, index) in popularItems.data" :key="item.id" class="transition hover:bg-slate-50">
                            <td class="table-td text-lg">{{ rank(index) }}</td>
                            <td class="table-td">
                                <div class="min-w-[200px]">
                                    <p class="font-semibold text-slate-800">{{ item.name }}</p>
                                    <div class="mt-1.5 flex h-1.5 w-full max-w-[220px] overflow-hidden rounded-full bg-slate-100">
                                        <div
                                            class="rounded-full bg-gradient-to-r from-indigo-500 to-indigo-600 transition-all"
                                            :style="{ width: Math.max(4, (Number(item.total_orders ?? 0) / maxCount) * 100) + '%' }"
                                        />
                                    </div>
                                </div>
                            </td>
                            <td class="table-td text-slate-400">{{ item.category?.name ?? '-' }}</td>
                            <td class="table-td text-right">
                                <span class="badge bg-indigo-50 text-indigo-700">{{ item.total_orders ?? 0 }}x</span>
                            </td>
                            <td class="table-td text-right font-semibold text-slate-800">{{ item.total_qty ?? 0 }}</td>
                        </tr>
                        <tr v-if="!popularItems.data.length">
                            <td colspan="5" class="px-6 py-16 text-center">
                                <p class="text-4xl">🏆</p>
                                <p class="mt-2 font-medium text-slate-500">Belum ada data penjualan</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="popularItems.links" :from="popularItems.from" :to="popularItems.to" :total="popularItems.total" />
        </div>
    </div>
</template>