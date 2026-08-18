<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import FormGroup from '../../../Components/FormGroup.vue';

const props = defineProps({
    ingredients: Array,
    menuItems: Array,
    type: String,
});

const types = {
    in: { label: 'Barang Masuk', hint: 'Stok akan bertambah', chip: 'bg-emerald-50 text-emerald-700', icon: 'M5 13l4 4L19 7' },
    out: { label: 'Barang Keluar', hint: 'Stok akan berkurang', chip: 'bg-rose-50 text-rose-700', icon: 'M5 13h14' },
    adjustment: { label: 'Penyesuaian Stok', hint: 'Stok akan disesuaikan dengan jumlah yang diisi', chip: 'bg-amber-50 text-amber-700', icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.4a2 2 0 112.8 2.8L11 17l-4 1 1-4 9.6-9.4z' },
};

const meta = computed(() => types[props.type] ?? types.in);

const form = useForm({
    type: props.type,
    ingredient_id: '',
    quantity: '',
    reference: '',
    notes: '',
});

const submit = () => {
    form.post(route('admin.stock-logs.store'));
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="mx-auto max-w-2xl">
        <PageHeader :title="`${meta.label}`" subtitle="Catat mutasi stok bahan baku" />

        <div class="mb-4">
            <div class="flex rounded-xl bg-white p-1 shadow-sm ring-1 ring-slate-200">
                <a
                    v-for="t in ['in', 'out', 'adjustment']"
                    :key="t"
                    :href="route('admin.stock-logs.create', { type: t })"
                    class="flex-1 rounded-lg px-4 py-2 text-center text-sm font-semibold transition"
                    :class="type === t ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50'"
                >
                    {{ types[t].label }}
                </a>
            </div>
        </div>

        <form class="card space-y-5 p-6" @submit.prevent="submit">
            <div class="flex items-start gap-3 rounded-xl p-4" :class="meta.chip">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="meta.icon" />
                </svg>
                <p class="text-sm font-medium">{{ meta.hint }}</p>
            </div>

            <FormGroup name="ingredient_id" label="Bahan Baku" :error="form.errors.ingredient_id" required>
                <select v-model="form.ingredient_id" name="ingredient_id" class="input">
                    <option value="">Pilih Bahan</option>
                    <option v-for="ingredient in ingredients" :key="ingredient.id" :value="ingredient.id">
                        {{ ingredient.name }} (Stok: {{ Number(ingredient.current_stock).toLocaleString('id-ID') }} {{ ingredient.unit }})
                    </option>
                </select>
            </FormGroup>
            <FormGroup name="quantity" label="Jumlah" :error="form.errors.quantity" required>
                <input v-model="form.quantity" type="number" name="quantity" min="0.01" step="0.01" class="input" placeholder="0.00">
            </FormGroup>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <FormGroup name="reference" label="Referensi" :error="form.errors.reference">
                    <input v-model="form.reference" type="text" name="reference" class="input" placeholder="No. nota / faktur">
                </FormGroup>
                <FormGroup name="notes" label="Catatan" :error="form.errors.notes">
                    <input v-model="form.notes" type="text" name="notes" class="input" placeholder="Opsional">
                </FormGroup>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a :href="route('admin.stock-logs.index')" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</template>