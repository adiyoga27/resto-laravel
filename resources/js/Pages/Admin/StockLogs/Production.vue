<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import FormGroup from '../../../Components/FormGroup.vue';

const props = defineProps({
    menuItems: Array,
});

const form = useForm({
    menu_item_id: '',
    quantity: 1,
});

const selectedMenu = computed(() => props.menuItems.find((m) => m.id === Number(form.menu_item_id)));

const recipe = computed(() => {
    const selected = selectedMenu.value;
    if (!selected) return [];
    return (selected.recipe_items ?? []).map((item) => ({
        name: item.ingredient?.name ?? 'Bahan terhapus',
        unit: item.ingredient?.unit ?? '-',
        qty: Number(item.quantity),
        stock: Number(item.ingredient?.current_stock ?? 0),
    }));
});

const usage = computed(() => {
    const qty = Number(form.quantity) || 1;
    return recipe.value.map((r) => ({
        ...r,
        used: r.qty * qty,
        remaining: r.stock - r.qty * qty,
    }));
});

const hasNegativeStock = computed(() => usage.value.some((u) => u.remaining < 0));

const submit = () => {
    form.post(route('admin.stock-logs.store-production'), {
        preserveScroll: true,
    });
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="mx-auto max-w-3xl">
        <PageHeader title="Produksi Menu" subtitle="Kurangi bahan baku + tambah stok produk jadi" />
        <form class="card space-y-6 p-6" @submit.prevent="submit">
            <FormGroup name="menu_item_id" label="Pilih Menu" :error="form.errors.menu_item_id" required>
                <select v-model="form.menu_item_id" name="menu_item_id" class="input">
                    <option value="">Pilih Menu untuk Diproduksi</option>
                    <option v-for="menu in menuItems" :key="menu.id" :value="menu.id">
                        {{ menu.name }} ({{ menu.category?.name ?? '-' }}) — Stok Produk: {{ menu.stock }}
                    </option>
                </select>
            </FormGroup>

            <div v-if="recipe.length" class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
                <h3 class="mb-3 text-sm font-bold tracking-wide text-slate-600 uppercase">Komposisi per 1 Porsi</h3>
                <div class="overflow-x-auto rounded-xl bg-white">
                    <table class="w-full text-left">
                        <thead class="border-b border-slate-100">
                            <tr>
                                <th class="table-th">Bahan</th>
                                <th class="table-th">Satuan</th>
                                <th class="table-th text-right">Qty</th>
                                <th class="table-th text-right">Stok Tersedia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="r in recipe" :key="r.name">
                                <td class="table-td font-medium text-slate-800">{{ r.name }}</td>
                                <td class="table-td text-slate-400">{{ r.unit }}</td>
                                <td class="table-td text-right">{{ r.qty.toFixed(2) }}</td>
                                <td class="table-td text-right">{{ r.stock.toFixed(2) }}</td>
                            </tr>
                            <tr v-if="!recipe.length">
                                <td colspan="4" class="px-6 py-6 text-center text-sm text-slate-400">Menu ini belum memiliki resep</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <FormGroup name="quantity" label="Jumlah Produksi (porsi)" :error="form.errors.quantity" required>
                <input v-model.number="form.quantity" type="number" name="quantity" min="1" class="input">
            </FormGroup>

            <div v-if="usage.length" class="rounded-2xl bg-violet-50 p-5 ring-1 ring-violet-200">
                <h3 class="mb-3 text-sm font-bold tracking-wide text-violet-700 uppercase">Total Bahan Yang Digunakan</h3>
                <div class="overflow-x-auto rounded-xl bg-white">
                    <table class="w-full text-left">
                        <thead class="border-b border-slate-100">
                            <tr>
                                <th class="table-th">Bahan</th>
                                <th class="table-th text-right">Total Digunakan</th>
                                <th class="table-th text-right">Sisa Stok</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="u in usage" :key="u.name">
                                <td class="table-td font-medium text-slate-800">{{ u.name }}</td>
                                <td class="table-td text-right">{{ u.used.toFixed(2) }}</td>
                                <td class="table-td text-right" :class="u.remaining < 0 ? 'font-bold text-rose-600' : 'text-emerald-600'">
                                    {{ u.remaining.toFixed(2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="hasNegativeStock" class="mt-3 rounded-xl bg-rose-100 px-4 py-3 text-sm font-medium text-rose-700">
                    ⚠️ Ada bahan yang stoknya tidak mencukupi. Pastikan stok bahan cukup sebelum produksi.
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a :href="route('admin.stock-logs.index')" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-warning" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Produksi
                </button>
            </div>
        </form>
    </div>
</template>