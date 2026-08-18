<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import FormGroup from '../../../Components/FormGroup.vue';

const form = useForm({
    name: '',
    description: '',
    sort_order: 0,
});

const submit = () => {
    form.post(route('admin.menu-categories.store'));
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="mx-auto max-w-2xl">
        <PageHeader title="Tambah Kategori" subtitle="Buat pengelompokan menu baru" />
        <form class="card space-y-5 p-6" @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <FormGroup name="name" label="Nama Kategori" :error="form.errors.name" required>
                    <input v-model="form.name" type="text" name="name" class="input" placeholder="cth: Makanan, Minuman">
                </FormGroup>
                <FormGroup name="sort_order" label="Urutan" :error="form.errors.sort_order">
                    <input v-model.number="form.sort_order" type="number" name="sort_order" min="0" class="input">
                </FormGroup>
            </div>
            <FormGroup name="description" label="Deskripsi" :error="form.errors.description">
                <textarea v-model="form.description" name="description" rows="3" class="input" placeholder="Opsional" />
            </FormGroup>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a :href="route('admin.menu-categories.index')" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</template>