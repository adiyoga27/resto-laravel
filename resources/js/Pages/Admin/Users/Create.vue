<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import FormGroup from '../../../Components/FormGroup.vue';
import FormToggle from '../../../Components/FormToggle.vue';

const props = defineProps({
    roles: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: props.roles[0]?.value ?? 'kasir',
    phone: '',
});

const submit = () => {
    form.post(route('admin.users.store'));
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="mx-auto max-w-2xl">
        <PageHeader title="Tambah User" subtitle="Buat akun baru untuk pengguna" />
        <form class="card space-y-5 p-6" @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <FormGroup name="name" label="Nama Lengkap" :error="form.errors.name" required>
                    <input v-model="form.name" type="text" name="name" class="input" placeholder="Nama pengguna">
                </FormGroup>
                <FormGroup name="role" label="Role" :error="form.errors.role" required>
                    <select v-model="form.role" name="role" class="input">
                        <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                    </select>
                </FormGroup>
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <FormGroup name="email" label="Email" :error="form.errors.email" required>
                    <input v-model="form.email" type="email" name="email" class="input" placeholder="nama@resto.id">
                </FormGroup>
                <FormGroup name="phone" label="No. HP" :error="form.errors.phone">
                    <input v-model="form.phone" type="text" name="phone" class="input" placeholder="08xxxxxxxxxx">
                </FormGroup>
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <FormGroup name="password" label="Password" :error="form.errors.password" required>
                    <input v-model="form.password" type="password" name="password" class="input" placeholder="Minimal 8 karakter" autocomplete="new-password">
                </FormGroup>
                <FormGroup name="password_confirmation" label="Konfirmasi Password">
                    <input v-model="form.password_confirmation" type="password" name="password_confirmation" class="input" placeholder="Ulangi password">
                </FormGroup>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a :href="route('admin.users.index')" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Simpan User
                </button>
            </div>
        </form>
    </div>
</template>