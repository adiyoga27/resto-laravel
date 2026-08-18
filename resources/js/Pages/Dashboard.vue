<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import {
    Chart as ChartJS,
    registerables,
} from 'chart.js';
import AppLayout from '@/Layouts/AppLayout.vue';
import { money } from '@/utils/format';

ChartJS.register(...registerables);

const props = defineProps({
    totalOrders: Number,
    totalRevenue: Number,
    activeOrders: Number,
    totalCustomers: Number,
    dailyData: Object,
    weeklyData: Object,
    monthlyData: Object,
});

const periods = [
    { key: 'daily', label: 'Harian', title: 'Grafik Omzet Harian (30 Hari Terakhir)' },
    { key: 'weekly', label: 'Mingguan', title: 'Grafik Omzet Mingguan (12 Minggu Terakhir)' },
    { key: 'monthly', label: 'Bulanan', title: 'Grafik Omzet Bulanan' },
];

const activePeriod = ref('daily');
const chartRef = ref(null);
let chartInstance = null;

const dataset = computed(() => props[`${activePeriod.value}Data`] ?? { labels: [], revenue: [], orders: [] });

const stats = computed(() => [
    { label: 'Total Orders', value: props.totalOrders, icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', color: 'indigo' },
    { label: 'Revenue', value: money(props.totalRevenue), icon: 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', color: 'emerald' },
    { label: 'Order Aktif', value: props.activeOrders, icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', color: 'amber' },
    { label: 'Customers', value: props.totalCustomers, icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', color: 'cyan' },
]);

const statColors = {
    indigo: 'from-indigo-500 to-indigo-600 shadow-indigo-200',
    emerald: 'from-emerald-500 to-emerald-600 shadow-emerald-200',
    amber: 'from-amber-500 to-amber-600 shadow-amber-200',
    cyan: 'from-cyan-500 to-cyan-600 shadow-cyan-200',
};

const renderChart = () => {
    if (chartInstance) chartInstance.destroy();

    const ctx = chartRef.value?.getContext('2d');
    if (!ctx) return;

    chartInstance = new ChartJS(ctx, {
        type: 'bar',
        data: {
            labels: dataset.value.labels,
            datasets: [
                {
                    label: 'Omzet (Rp)',
                    data: dataset.value.revenue,
                    backgroundColor: 'rgba(99, 102, 241, 0.75)',
                    hoverBackgroundColor: 'rgba(79, 70, 229, 0.9)',
                    borderRadius: 6,
                    yAxisID: 'y',
                },
                {
                    label: 'Jumlah Order',
                    data: dataset.value.orders,
                    type: 'line',
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#f59e0b',
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'y1',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, padding: 20, boxWidth: 8 } },
                tooltip: {
                    callbacks: {
                        label: (ctx) => (ctx.dataset.label === 'Omzet (Rp)' ? `Omzet: ${money(ctx.raw)}` : `Order: ${ctx.raw}`),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (v) => `Rp ${(v / 1000000).toFixed(0)}jt` },
                    grid: { color: '#f1f5f9' },
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { drawOnChartArea: false },
                },
                x: {
                    grid: { display: false },
                    ticks: activePeriod.value === 'daily' ? { maxTicksLimit: 15, autoSkip: true } : {},
                },
            },
        },
    });
};

watch(activePeriod, () => renderChart());
onMounted(() => renderChart());

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="space-y-6">
        <!-- Stat cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div v-for="stat in stats" :key="stat.label" class="card flex items-center gap-4 p-5">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br text-white shadow-lg" :class="statColors[stat.color]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="stat.icon" /></svg>
                </div>
                <div class="min-w-0">
                    <p class="truncate text-xs font-semibold tracking-wide text-slate-500 uppercase">{{ stat.label }}</p>
                    <p class="text-xl font-extrabold tracking-tight text-slate-900">{{ stat.value }}</p>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="card">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="font-bold text-slate-900">{{ periods.find((p) => p.key === activePeriod).title }}</h3>
                <div class="flex items-center gap-2">
                    <div class="flex rounded-xl bg-slate-100 p-1">
                        <button
                            v-for="period in periods"
                            :key="period.key"
                            class="rounded-lg px-4 py-1.5 text-xs font-semibold transition"
                            :class="activePeriod === period.key ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                            @click="activePeriod = period.key"
                        >
                            {{ period.label }}
                        </button>
                    </div>
                    <a :href="route('admin.dashboard.export')" class="btn-secondary text-xs">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Export Excel
                    </a>
                </div>
            </div>
            <div class="h-[380px] p-5">
                <canvas ref="chartRef" />
            </div>
        </div>
    </div>
</template>