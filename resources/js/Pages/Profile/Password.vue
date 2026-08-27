<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import FormGroup from '../../Components/FormGroup.vue';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('profile.password.update'), {
        onSuccess: () => form.reset(),
    });
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="mx-auto max-w-2xl">
        <PageHeader title="Ganti Password" subtitle="Perbarui password akun Anda" />
        <form class="card space-y-5 p-6" @submit.prevent="submit">
            <FormGroup name="current_password" label="Password Saat Ini" :error="form.errors.current_password" required>
                <input v-model="form.current_password" type="password" name="current_password" class="input" placeholder="Password saat ini" autocomplete="current-password">
            </FormGroup>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <FormGroup name="password" label="Password Baru" :error="form.errors.password" required hint="Minimal 8 karakter">
                    <input v-model="form.password" type="password" name="password" class="input" placeholder="Password baru" autocomplete="new-password">
                </FormGroup>
                <FormGroup name="password_confirmation" label="Konfirmasi Password" :error="form.errors.password_confirmation" required>
                    <input v-model="form.password_confirmation" type="password" name="password_confirmation" class="input" placeholder="Ulangi password" autocomplete="new-password">
                </FormGroup>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Ubah Password
                </button>
            </div>
        </form>
    </div>
</template>