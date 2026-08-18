<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import FormGroup from '../../Components/FormGroup.vue';

const form = useForm({
    date: new Date().toISOString().slice(0, 10),
    description: '',
    type: 'debit',
    amount: '',
    reference: '',
});

const submit = () => {
    form.post(route('reports.cash-flow.store'));
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="mx-auto max-w-2xl">
        <PageHeader title="Entri Arus Kas Baru" subtitle="Catat pemasukan atau pengeluaran" />
        <form class="card space-y-5 p-6" @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <FormGroup name="date" label="Tanggal" :error="form.errors.date" required>
                    <input v-model="form.date" type="date" name="date" class="input">
                </FormGroup>
                <FormGroup name="type" label="Tipe" :error="form.errors.type" required>
                    <select v-model="form.type" name="type" class="input">
                        <option value="debit">Debit (Masuk)</option>
                        <option value="kredit">Kredit (Keluar)</option>
                    </select>
                </FormGroup>
            </div>
            <FormGroup name="description" label="Keterangan" :error="form.errors.description" required>
                <input v-model="form.description" type="text" name="description" class="input" placeholder="cth: Pembelian bahan, Modal usaha">
            </FormGroup>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <FormGroup name="amount" label="Jumlah (Rp)" :error="form.errors.amount" required>
                    <input v-model="form.amount" type="number" name="amount" min="0.01" step="0.01" class="input">
                </FormGroup>
                <FormGroup name="reference" label="Referensi" :error="form.errors.reference">
                    <input v-model="form.reference" type="text" name="reference" class="input" placeholder="Opsional">
                </FormGroup>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a :href="route('reports.cash-flow')" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</template>