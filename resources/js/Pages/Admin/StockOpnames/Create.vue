<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import FormGroup from '../../../Components/FormGroup.vue';

const props = defineProps({
    ingredients: Array,
});

const actualStock = {};
props.ingredients.forEach((ingredient) => {
    actualStock[ingredient.id] = ingredient.current_stock;
});

const form = useForm({
    date: new Date().toISOString().slice(0, 10),
    notes: '',
    actual_stock: actualStock,
});

const submit = () => {
    form.post(route('admin.stock-opnames.store'));
};

const difference = (ingredient) => {
    const actual = Number(form.actual_stock[ingredient.id]);
    return actual - Number(ingredient.current_stock);
};

const diffClass = (ingredient) => {
    const diff = difference(ingredient);
    if (diff < 0) return 'text-rose-600';
    if (diff > 0) return 'text-emerald-600';
    return 'text-slate-400';
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div>
        <PageHeader title="Buat Stok Opname" subtitle="Input stok fisik aktual bahan baku" />
        <form class="card" @submit.prevent="submit">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row">
                <div class="sm:w-56">
                    <label class="label">Tanggal Opname</label>
                    <input v-model="form.date" type="date" name="date" class="input">
                </div>
                <div class="flex-1">
                    <label class="label">Catatan</label>
                    <input v-model="form.notes" type="text" name="notes" class="input" placeholder="Opsional">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="table-th">Bahan Baku</th>
                            <th class="table-th">Satuan</th>
                            <th class="table-th text-right">Stok Sistem</th>
                            <th class="table-th text-right w-40">Stok Fisik</th>
                            <th class="table-th text-right">Selisih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="ingredient in ingredients" :key="ingredient.id" class="transition hover:bg-slate-50">
                            <td class="table-td font-medium text-slate-800">{{ ingredient.name }}</td>
                            <td class="table-td text-slate-400">{{ ingredient.unit }}</td>
                            <td class="table-td text-right">{{ Number(ingredient.current_stock).toLocaleString('id-ID') }}</td>
                            <td class="table-td">
                                <input
                                    v-model.number="form.actual_stock[ingredient.id]"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="input text-right"
                                    :class="form.errors[`actual_stock.${ingredient.id}`] ? 'ring-rose-300' : ''"
                                >
                            </td>
                            <td class="table-td text-right font-bold" :class="diffClass(ingredient)">
                                {{ difference(ingredient).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 p-6">
                <a :href="route('admin.stock-opnames.index')" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Simpan Opname
                </button>
            </div>
        </form>
    </div>
</template>