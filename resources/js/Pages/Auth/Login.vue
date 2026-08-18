<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import FormInput from '@/Components/FormInput.vue';

const page = usePage();

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

defineOptions({ layout: AuthLayout });
</script>

<template>
    <form class="space-y-5" @submit.prevent="submit">
        <FormInput
            v-model="form.email"
            name="email"
            label="Email"
            type="email"
            autocomplete="email"
            placeholder="nama@resto.id"
            :error="form.errors.email"
            required
        />
        <FormInput
            v-model="form.password"
            name="password"
            label="Password"
            type="password"
            autocomplete="current-password"
            placeholder="••••••••"
            :error="form.errors.password"
            required
        />
        <button type="submit" class="btn-primary w-full py-3" :disabled="form.processing">
            <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
            Sign In
        </button>
    </form>
</template>