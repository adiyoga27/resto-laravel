<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import FormGroup from '../../../Components/FormGroup.vue';

const form = useForm({
    table_number: '',
    capacity: 2,
});

const submit = () => {
    form.post(route('admin.tables.store'));
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="mx-auto max-w-xl">
        <PageHeader title="Tambah Meja" subtitle="Tambahkan meja baru ke restoran" />
        <form class="card space-y-5 p-6" @submit.prevent="submit">
            <FormGroup name="table_number" label="Nomor Meja" :error="form.errors.table_number" required>
                <input v-model="form.table_number" type="text" name="table_number" class="input" placeholder="cth: A-01 / 1 / VIP 3">
            </FormGroup>
            <FormGroup name="capacity" label="Kapasitas (kursi)" :error="form.errors.capacity" required>
                <input v-model.number="form.capacity" type="number" name="capacity" min="1" class="input">
            </FormGroup>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a :href="route('admin.tables.index')" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</template>