<script setup>
import { computed, onMounted, ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { money } from '../../utils/format';
import { setupQzSecurity, loadQzScript, printerSettings, buildReceiptData, printRaw, showPrintError } from '../../utils/qz';

const props = defineProps({
    menuItems: Object,
    tables: Array,
    activeOrders: Array,
    printer: Object,
    successOrder: Object,
});

const cart = ref([]);
const selectedOrderType = ref('dine-in');
const selectedTable = ref(null);
const discount = ref(0);
const editingOrderId = ref(null);
const search = ref('');
const customerName = ref('');
const customerPhone = ref('');
const notes = ref('');
const paymentMethod = ref('cash');
const bankName = ref('');
const accountNumber = ref('');
const showSuccess = ref(false);

const form = useForm({});

const filteredCategories = computed(() => {
    const results = {};
    Object.entries(props.menuItems).forEach(([categoryName, items]) => {
        const filtered = items.filter((item) => item.name.toLowerCase().includes(search.value.toLowerCase()));
        if (filtered.length) results[categoryName] = filtered;
    });
    return results;
});

const cartSubtotal = computed(() => cart.value.reduce((sum, item) => sum + item.price * item.qty, 0));
const afterDiscount = computed(() => Math.max(0, cartSubtotal.value - (discount.value || 0)));
const cartTax = computed(() => afterDiscount.value * 0.11);
const cartTotal = computed(() => afterDiscount.value + cartTax.value);
const cartCount = computed(() => cart.value.reduce((sum, item) => sum + item.qty, 0));
const cartTitle = computed(() => (editingOrderId.value ? 'Edit Pesanan' : 'Pesanan Baru'));
const activeTables = computed(() => props.tables.filter((t) => t.status === 'kosong'));
const occupiedTables = computed(() => props.tables.filter((t) => t.status !== 'kosong'));

const addToCart = (item) => {
    const existing = cart.value.find((i) => i.id === item.id);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.value.push({ id: item.id, name: item.name, price: Number(item.price), qty: 1 });
    }
};

const setTable = (id) => {
    selectedTable.value = id;
};

const setOrderType = (type) => {
    selectedOrderType.value = type;
    if (type !== 'dine-in') {
        selectedTable.value = null;
    }
};

const changeQty = (index, delta) => {
    cart.value[index].qty += delta;
    if (cart.value[index].qty <= 0) {
        cart.value.splice(index, 1);
    }
};

const removeItem = (index) => {
    cart.value.splice(index, 1);
};

const resetCart = () => {
    cart.value = [];
    selectedOrderType.value = 'dine-in';
    selectedTable.value = null;
    discount.value = 0;
    editingOrderId.value = null;
    customerName.value = '';
    customerPhone.value = '';
    notes.value = '';
    paymentMethod.value = 'cash';
    showSuccess.value = false;
};

const submitOrder = () => {
    if (!selectedOrderType.value) {
        alert('Pilih tipe order terlebih dahulu');
        return;
    }
    if (selectedOrderType.value === 'dine-in' && !selectedTable.value) {
        alert('Pilih meja untuk dine-in');
        return;
    }

    const items = cart.value.map((item) => ({
        menu_item_id: item.id,
        quantity: item.qty,
        notes: item.notes ?? '',
    }));

    let paymentReference = '';
    if (paymentMethod.value === 'transfer') {
        paymentReference = `${bankName.value} - ${accountNumber.value}`;
    }

    const payload = {
        order_type: selectedOrderType.value,
        restaurant_table_id: selectedTable.value,
        items,
        customer_name: customerName.value,
        customer_phone: customerPhone.value,
        payment_method: paymentMethod.value,
        payment_amount: cartTotal.value,
        discount: discount.value,
        payment_reference: paymentReference,
        notes: notes.value,
    };

    if (editingOrderId.value) {
        form.put(route('pos.orders.update', editingOrderId.value), payload);
    } else {
        form.post(route('pos.orders.store'), payload);
    }
};

const editOrder = (order) => {
    editingOrderId.value = order.id;
    cart.value = order.order_items.map((i) => ({
        id: i.menu_item_id,
        name: i.menu_item?.name ?? 'Menu',
        price: Number(i.price),
        qty: i.quantity,
    }));
    discount.value = 0;
    resetCartStateKeepEditing(order);
};

const resetCartStateKeepEditing = (order) => {
    if (order.restaurant_table_id) {
        selectedTable.value = order.restaurant_table_id;
        selectedOrderType.value = 'dine-in';
    } else {
        selectedOrderType.value = order.order_type ?? 'dine-in';
    }
};

const completeOrder = (order) => {
    router.post(route('pos.orders.complete', order.id), {}, { preserveScroll: true });
};

const cancelOrder = (order) => {
    router.post(route('pos.orders.cancel', order.id), {}, { preserveScroll: true });
};

const printSuccessReceipt = async () => {
    if (!props.successOrder) return;
    try {
        const res = await fetch(route('pos.orders.receipt', props.successOrder.id));
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const receiptData = await res.json();
        await setupQzSecurity();
        const data = buildReceiptData(receiptData);
        const settings = printerSettings(props.printer, window.localStorage);
        await printRaw(data, settings);
    } catch (err) {
        showPrintError(err);
    }
};

const printTestPage = async () => {
    try {
        await setupQzSecurity();
        const settings = printerSettings(props.printer, window.localStorage);
        const W = 48;
        const lines = [
            '\x1B@',
            '\x1Ba\x01',
            '=== TES PRINTER ===',
            '80mm ESC/POS via QZ Tray',
            '\x1Ba\x00',
            '-'.repeat(W),
            'Host: ' + settings.host + ':' + settings.port,
            'Jika tulisan ini tercetak,',
            'printer siap digunakan.',
            '',
            '\x1DV\x42\x01',
        ];
        await printRaw(lines.join('\n'), settings);
    } catch (err) {
        showPrintError(err);
    }
};

onMounted(() => {
    loadQzScript().catch(() => {});
    if (props.successOrder) {
        showSuccess.value = true;
    }
});

const emojis = ['🍜', '🍛', '🍗', '🥩', '🥤', '☕', '🍰', '🍟', '🍕', '🥗'];
const imageUrl = (path) => (path ? `/storage/${path}` : null);

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="flex flex-col gap-4 xl:flex-row">
        <!-- Left: menu -->
        <div class="flex min-w-0 flex-1 flex-col gap-4">
            <div class="card p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex-1">
                        <label class="label">Tipe Order</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                v-for="t in [
                                    { k: 'dine-in', label: '🍽️ Dine In' },
                                    { k: 'delivery', label: '🛵 Delivery' },
                                    { k: 'pickup', label: '🛍️ Pickup' },
                                ]"
                                :key="t.k"
                                type="button"
                                class="rounded-xl border-2 px-3 py-2 text-sm font-semibold transition"
                                :class="selectedOrderType === t.k ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-400 hover:border-indigo-300 hover:text-indigo-500'"
                                @click="setOrderType(t.k)"
                            >
                                {{ t.label }}
                            </button>
                        </div>
                    </div>
                    <div v-if="selectedOrderType === 'dine-in'" class="flex-1">
                        <label class="label">Pilih Meja</label>
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                v-for="table in activeTables"
                                :key="table.id"
                                type="button"
                                class="rounded-lg border-2 px-3 py-1.5 text-xs font-semibold transition"
                                :class="selectedTable === table.id ? 'border-indigo-500 bg-indigo-600 text-white' : 'border-slate-200 text-slate-600 hover:border-indigo-300'"
                                @click="setTable(table.id)"
                            >
                                {{ table.table_number }}
                            </button>
                            <button
                                v-for="table in occupiedTables"
                                :key="table.id"
                                type="button"
                                disabled
                                class="rounded-lg border-2 border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 opacity-70"
                                :title="`Meja ${table.table_number} terisi`"
                            >
                                {{ table.table_number }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <input v-model="customerName" type="text" class="input" placeholder="Nama customer">
                    <input v-model="customerPhone" type="text" class="input" placeholder="No. HP customer">
                </div>
            </div>

            <div class="card flex flex-1 flex-col">
                <div class="border-b border-slate-100 p-4">
                    <div class="relative">
                        <svg class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input v-model="search" type="text" class="input pl-10" placeholder="Cari menu...">
                    </div>
                </div>
                <div class="space-y-6 p-4">
                    <div v-for="(items, categoryName) in filteredCategories" :key="categoryName">
                        <div class="mb-3 flex items-center gap-2 border-b-2 border-slate-200 pb-2">
                            <span class="text-sm font-bold tracking-wide text-slate-500 uppercase">{{ categoryName }}</span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">{{ items.length }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-3 2xl:grid-cols-4">
                            <button
                                v-for="item in items"
                                :key="item.id"
                                type="button"
                                class="group overflow-hidden rounded-2xl border-2 border-slate-100 bg-white text-left transition-all hover:-translate-y-0.5 hover:border-indigo-400 hover:shadow-lg hover:shadow-indigo-100 active:scale-95"
                                @click="addToCart(item)"
                            >
                                <div class="flex h-20 items-center justify-center overflow-hidden bg-slate-50">
                                    <img v-if="imageUrl(item.image)" :src="imageUrl(item.image)" class="h-full w-full object-cover" :alt="item.name">
                                    <span v-else class="text-3xl">{{ emojis[item.id % 10] }}</span>
                                </div>
                                <div class="p-3">
                                    <p class="truncate text-sm font-bold text-slate-800">{{ item.name }}</p>
                                    <span class="mt-1 inline-block rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-600">{{ money(item.price) }}</span>
                                </div>
                            </button>
                        </div>
                    </div>
                    <div v-if="!Object.keys(filteredCategories).length" class="py-20 text-center text-slate-400">
                        <p class="text-4xl">🔍</p>
                        <p class="mt-2 font-medium">Menu tidak ditemukan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: cart -->
        <div class="flex w-full shrink-0 flex-col gap-4 xl:w-[400px]">
            <!-- Active orders -->
            <div v-if="activeOrders.length" class="card">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <p class="text-sm font-bold text-slate-700">📋 Order Aktif</p>
                    <span class="badge bg-indigo-50 text-indigo-700">{{ activeOrders.length }}</span>
                </div>
                <div class="max-h-40 space-y-1 overflow-y-auto p-2">
                    <div v-for="order in activeOrders" :key="order.id" class="flex items-center justify-between rounded-xl p-2 transition hover:bg-slate-50">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">#{{ order.order_number }}</p>
                            <p class="text-xs text-slate-400">
                                {{ order.restaurant_table ? `Meja ${order.restaurant_table.table_number}` : order.order_type }}
                                · {{ order.order_items.reduce((s, i) => s + i.quantity, 0) }} item
                                · <span class="font-semibold text-indigo-600">{{ money(order.total) }}</span>
                            </p>
                        </div>
                        <div class="flex shrink-0 gap-1">
                            <button class="rounded-lg bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100" @click="editOrder(order)">Lanjutkan</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart -->
            <div class="card">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <p class="text-sm font-bold text-slate-800">🛒 {{ cartTitle }}</p>
                    <div class="flex items-center gap-2">
                        <button class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-200" title="Tes Printer" @click="printTestPage">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        </button>
                        <button v-if="editingOrderId" class="rounded-lg bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-100" @click="resetCart">Batal</button>
                        <span class="badge bg-indigo-50 text-indigo-700">{{ cartCount }} item</span>
                    </div>
                </div>

                <div class="space-y-2 p-3">
                    <div v-for="(item, index) in cart" :key="index" class="rounded-xl border border-slate-100 p-3">
                        <div class="mb-2 flex items-start justify-between">
                            <p class="text-sm font-semibold text-slate-800">{{ item.name }}</p>
                            <p class="text-sm font-bold text-indigo-600">{{ money(item.price * item.qty) }}</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <button class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-slate-200 text-indigo-600 transition hover:border-indigo-500 hover:bg-indigo-50" @click="changeQty(index, -1)">−</button>
                                <span class="w-6 text-center text-sm font-bold">{{ item.qty }}</span>
                                <button class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-slate-200 text-indigo-600 transition hover:border-indigo-500 hover:bg-indigo-50" @click="changeQty(index, 1)">+</button>
                            </div>
                            <button class="text-xs font-medium text-rose-500 transition hover:text-rose-600" @click="removeItem(index)">Hapus</button>
                        </div>
                    </div>
                    <div v-if="!cart.length" class="flex h-full flex-col items-center justify-center py-16 text-slate-300">
                        <p class="text-5xl">🛍️</p>
                        <p class="mt-3 font-semibold text-slate-400">Belum Ada Pesanan</p>
                        <p class="text-xs">Klik menu untuk menambah pesanan</p>
                    </div>
                </div>

                <div class="border-t border-slate-100 bg-slate-50/60 p-4">
                    <div class="space-y-1.5 text-sm">
                        <div class="flex justify-between text-slate-500">
                            <span>Subtotal</span><span class="font-semibold">{{ money(cartSubtotal) }}</span>
                        </div>
                        <div v-if="discount > 0" class="flex justify-between text-rose-500">
                            <span>Diskon</span><span class="font-semibold">-{{ money(discount) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Tax (11%)</span><span class="font-semibold">{{ money(cartTax) }}</span>
                        </div>
                        <div class="border-t-2 border-dashed border-slate-200 pt-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800">Total</span>
                                <span class="text-lg font-extrabold text-indigo-600">{{ money(cartTotal) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="label">Diskon (Rp)</label>
                            <input v-model.number="discount" type="number" min="0" class="input" placeholder="0">
                        </div>
                        <div>
                            <label class="label">Metode Pembayaran</label>
                            <select v-model="paymentMethod" class="input">
                                <option value="cash">Tunai</option>
                                <option value="qris">QRIS</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="card">Kartu</option>
                            </select>
                        </div>
                        <div v-if="paymentMethod === 'transfer'" class="grid grid-cols-2 gap-2">
                            <input v-model="bankName" type="text" class="input" placeholder="Nama Bank">
                            <input v-model="accountNumber" type="text" class="input" placeholder="No. Rekening">
                        </div>
                        <textarea v-model="notes" rows="2" class="input" placeholder="Catatan pesanan..."></textarea>
                        <button
                            type="button"
                            class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:-translate-y-0.5 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="cart.length === 0 || form.processing"
                            @click="submitOrder"
                        >
                            <svg v-if="form.processing" class="mr-2 inline h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                            {{ editingOrderId ? 'Update Order' : 'Proses Order' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success modal -->
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        leave-active-class="transition-opacity duration-200"
        leave-to-class="opacity-0"
    >
        <div v-if="showSuccess && successOrder" class="fixed inset-0 z-[80] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="resetCart" />
            <div class="relative w-full max-w-sm overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 p-8 text-center">
                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-white/20">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white">Order Berhasil!</h3>
                    <p class="text-sm text-indigo-200">#{{ successOrder.number }}</p>
                </div>
                <div class="p-6 text-center">
                    <div class="mb-6 flex items-start justify-center gap-10">
                        <div>
                            <p class="text-xs text-slate-400">Total</p>
                            <p class="text-lg font-extrabold text-slate-900">{{ money(successOrder.total) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Item</p>
                            <p class="text-lg font-extrabold text-slate-900">{{ successOrder.items }} item</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button class="btn-primary flex-1" @click="printSuccessReceipt">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            Cetak
                        </button>
                        <button class="btn-secondary flex-1" @click="resetCart">Lanjut</button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>