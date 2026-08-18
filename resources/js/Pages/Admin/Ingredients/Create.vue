<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import FormGroup from '../../../Components/FormGroup.vue';

const form = useForm({
    name: '',
    unit: '',
    current_stock: 0,
    min_stock: 0,
    cost_price: 0,
});

const submit = () => {
    form.post(route('admin.ingredients.store'));
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="mx-auto max-w-2xl">
        <PageHeader title="Tambah Bahan Baku" subtitle="Tambahkan bahan baku baru" />
        <form class="card space-y-5 p-6" @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <FormGroup name="name" label="Nama Bahan" :error="form.errors.name" required>
                    <input v-model="form.name" type="text" name="name" class="input" placeholder="cth: Beras, Ayam, Minyak Goreng">
                </FormGroup>
                <FormGroup name="unit" label="Satuan" :error="form.errors.unit" required>
                    <input v-model="form.unit" type="text" name="unit" class="input" placeholder="cth: kg, gram, liter, pcs">
                </FormGroup>
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <FormGroup name="current_stock" label="Stok Saat Ini" :error="form.errors.current_stock">
                    <input v-model="form.current_stock" type="number" name="current_stock" min="0" step="0.01" class="input">
                </FormGroup>
                <FormGroup name="min_stock" label="Stok Minimum" :error="form.errors.min_stock">
                    <input v-model="form.min_stock" type="number" name="min_stock" min="0" step="0.01" class="input">
                </FormGroup>
                <FormGroup name="cost_price" label="Harga Beli (Rp)" :error="form.errors.cost_price">
                    <input v-model="form.cost_price" type="number" name="cost_price" min="0" step="0.01" class="input">
                </FormGroup>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a :href="route('admin.ingredients.index')" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</template>