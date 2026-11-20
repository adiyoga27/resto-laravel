<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import FormGroup from '../../../Components/FormGroup.vue';

defineProps({
    users: Array,
});

const form = useForm({
    name: '',
    rate: 0,
    is_active: true,
    sort_order: 0,
    user_ids: [],
});

const submit = () => {
    form.post(route('admin.additional-costs.store'));
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="mx-auto max-w-xl">
        <PageHeader title="Tambah Biaya Tambahan" subtitle="Contoh: Service Charge 5%, Air Minum 1%" />
        <form class="card space-y-5 p-6" @submit.prevent="submit">
            <FormGroup name="name" label="Nama" :error="form.errors.name" required>
                <input v-model="form.name" type="text" name="name" class="input" placeholder="cth: Service Charge">
            </FormGroup>
            <FormGroup name="rate" label="Persentase (%)" :error="form.errors.rate" required>
                <input v-model.number="form.rate" type="number" name="rate" min="0" max="100" step="0.01" class="input" placeholder="cth: 5">
            </FormGroup>
            <FormGroup name="sort_order" label="Urutan" :error="form.errors.sort_order">
                <input v-model.number="form.sort_order" type="number" name="sort_order" min="0" class="input" placeholder="0">
            </FormGroup>
            <FormGroup name="user_ids" label="Dikenakan ke User (email login)" :error="form.errors.user_ids">
                <p class="mb-2 text-xs text-slate-500">Biaya ini otomatis ditambahkan ke order yang dibuat user terpilih selama status aktif. User yang tidak dipilih tidak dikenakan biaya.</p>
                <div class="max-h-64 divide-y divide-slate-100 overflow-y-auto rounded-xl border border-slate-200">
                    <label v-for="user in users" :key="user.id" class="flex cursor-pointer items-center gap-3 px-4 py-2.5 hover:bg-slate-50">
                        <input v-model="form.user_ids" type="checkbox" :value="user.id" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-slate-700">{{ user.email }}</span>
                            <span class="block truncate text-xs text-slate-500">{{ user.name }} · {{ user.role }}</span>
                        </span>
                    </label>
                    <p v-if="!users.length" class="px-4 py-3 text-sm text-slate-500">Belum ada user</p>
                </div>
            </FormGroup>
            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3">
                <input v-model="form.is_active" type="checkbox" name="is_active" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm font-semibold text-slate-700">Aktif (diterapkan ke user terpilih)</span>
            </label>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a :href="route('admin.additional-costs.index')" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</template>