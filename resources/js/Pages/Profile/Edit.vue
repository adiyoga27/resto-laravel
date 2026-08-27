<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import FormGroup from '../../Components/FormGroup.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone ?? '',
});

const submit = () => {
    form.put(route('profile.update'));
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="mx-auto max-w-2xl">
        <PageHeader title="Edit Profil" subtitle="Memperbarui data akun Anda" />
        <form class="card space-y-5 p-6" @submit.prevent="submit">
            <FormGroup name="name" label="Nama Lengkap" :error="form.errors.name" required>
                <input v-model="form.name" type="text" name="name" class="input" placeholder="Nama pengguna">
            </FormGroup>
            <FormGroup name="email" label="Email" :error="form.errors.email" required>
                <input v-model="form.email" type="email" name="email" class="input" placeholder="nama@resto.id">
            </FormGroup>
            <FormGroup name="phone" label="No. HP" :error="form.errors.phone">
                <input v-model="form.phone" type="text" name="phone" class="input" placeholder="08xxxxxxxxxx">
            </FormGroup>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</template>