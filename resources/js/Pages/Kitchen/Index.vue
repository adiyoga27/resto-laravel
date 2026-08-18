<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    orders: Array,
});

const orders = ref(props.orders);
const autoRefresh = ref(true);
let interval = null;

const statusMeta = {
    baru: { label: 'Baru', chip: 'bg-sky-50 text-sky-700 ring-sky-200', dot: 'bg-sky-500' },
    diproses: { label: 'Diproses', chip: 'bg-amber-50 text-amber-700 ring-amber-200', dot: 'bg-amber-500' },
    siap: { label: 'Siap', chip: 'bg-violet-50 text-violet-700 ring-violet-200', dot: 'bg-violet-500' },
};

const typeLabel = { 'dine-in': '🍽️ Dine In', delivery: '🛵 Delivery', pickup: '🛍️ Pickup' };

const fetchOrders = async () => {
    try {
        const res = await axios.get(route('kitchen.orders'));
        orders.value = res.data;
    } catch (e) {
        // keep existing
    }
};

const updateStatus = async (order, status) => {
    try {
        const res = await axios.patch(route('kitchen.orders.status', order.id), { status });
        const idx = orders.value.findIndex((o) => o.id === order.id);
        if (idx !== -1) orders.value[idx] = res.data.order;
        fetchOrders();
    } catch (e) {
        /* noop */
    }
};

const startPolling = () => {
    stopPolling();
    interval = setInterval(fetchOrders, 5000);
};

const stopPolling = () => {
    if (interval) clearInterval(interval);
    interval = null;
};

const toggleRefresh = () => {
    autoRefresh.value = !autoRefresh.value;
    if (autoRefresh.value) {
        fetchOrders();
        startPolling();
    } else {
        stopPolling();
    }
};

const actionFor = (order) => {
    if (order.order_status === 'baru') {
        return { status: 'diproses', label: 'Mulai Masak', chip: 'btn-warning' };
    }
    if (order.order_status === 'diproses') {
        return { status: 'siap', label: 'Siap', chip: 'btn-success' };
    }
    if (order.order_status === 'siap') {
        return { status: 'selesai', label: 'Antarkan', chip: 'btn-primary' };
    }
    return null;
};

const onVisibility = () => {
    if (document.hidden) {
        stopPolling();
    } else if (autoRefresh.value) {
        fetchOrders();
        startPolling();
    }
};

onMounted(() => {
    startPolling();
    document.addEventListener('visibilitychange', onVisibility);
});

onUnmounted(() => {
    stopPolling();
    document.removeEventListener('visibilitychange', onVisibility);
});

defineOptions({ layout: AppLayout });
</script>

<template>
    <div>
        <div class="mb-5 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-700">👨‍🍳 Antrian Dapur</p>
                <p class="text-xs text-slate-400">
                    {{ autoRefresh ? 'Auto-refresh setiap 5 detik' : 'Auto-refresh dimatikan' }}
                </p>
            </div>
            <button class="btn-secondary text-xs" @click="toggleRefresh">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5m11 6h-5v5M4.5 16a8 8 0 0113.5-4.5M5.5 8A8 8 0 0119 12.5" /></svg>
                {{ autoRefresh ? 'Pause' : 'Resume' }}
            </button>
        </div>

        <div v-if="orders.length" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="order in orders"
                :key="order.id"
                class="card overflow-hidden transition hover:shadow-md"
            >
                <div class="flex items-start justify-between border-b border-slate-100 bg-slate-50/60 px-5 py-4">
                    <div>
                        <p class="font-bold text-slate-800">#{{ order.order_number }}</p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ typeLabel[order.order_type] ?? order.order_type }}
                            <span v-if="order.restaurant_table" class="ml-2 rounded-md bg-indigo-50 px-1.5 py-0.5 font-semibold text-indigo-600">Meja {{ order.restaurant_table.table_number }}</span>
                        </p>
                    </div>
                    <span class="badge ring-1 ring-inset" :class="statusMeta[order.order_status]?.chip">
                        <span class="h-1.5 w-1.5 rounded-full" :class="statusMeta[order.order_status]?.dot" />
                        {{ statusMeta[order.order_status]?.label ?? order.order_status }}
                    </span>
                </div>

                <div class="px-5 py-4">
                    <ul class="mb-3 space-y-2">
                        <li v-for="item in order.order_items" :key="item.id" class="flex items-start justify-between gap-3 text-sm">
                            <span class="font-semibold text-slate-700">
                                {{ item.quantity }}x {{ item.menu_item?.name ?? 'Menu' }}
                            </span>
                            <span v-if="item.notes" class="max-w-[45%] truncate text-xs text-slate-400" :title="item.notes">({{ item.notes }})</span>
                        </li>
                    </ul>
                    <p v-if="order.notes" class="mb-3 rounded-lg bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700">📝 {{ order.notes }}</p>
                    <p class="mb-3 text-xs text-slate-400">
                        {{ new Date(order.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                        · {{ order.order_items.reduce((s, i) => s + i.quantity, 0) }} item
                    </p>
                    <button
                        v-if="actionFor(order)"
                        type="button"
                        class="btn w-full"
                        :class="actionFor(order).chip"
                        @click="updateStatus(order, actionFor(order).status)"
                    >
                        {{ actionFor(order).label }}
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="card p-16 text-center">
            <p class="text-5xl">✅</p>
            <p class="mt-3 text-lg font-semibold text-slate-500">Semua order selesai!</p>
            <p class="text-sm text-slate-400">Tidak ada pesanan yang menunggu di dapur</p>
        </div>
    </div>
</template>