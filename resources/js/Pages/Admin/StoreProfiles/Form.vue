<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import FormGroup from '../../../Components/FormGroup.vue';
import FormTextarea from '../../../Components/FormTextarea.vue';

const props = defineProps({
    profile: Object,
});

const form = useForm({
    store_name: props.profile?.store_name ?? '',
    store_address: props.profile?.store_address ?? '',
    store_phone: props.profile?.store_phone ?? '',
    is_active: props.profile?.is_active ?? true,
});

const isEdit = computed(() => !!props.profile);

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.store-profiles.update'));
    } else {
        form.post(route('admin.store-profiles.store'));
    }
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="mx-auto max-w-xl">
        <PageHeader title="Data Toko" :subtitle="isEdit ? `Memperbarui ${profile.store_name}` : 'Isi nama toko, alamat toko, dan nomor telepon toko'" />
        <form class="card space-y-5 p-6" @submit.prevent="submit">
            <FormGroup name="store_name" label="Nama Toko" :error="form.errors.store_name" required>
                <input v-model="form.store_name" type="text" name="store_name" class="input" placeholder="cth: Resto Nusantara">
            </FormGroup>
            <FormTextarea v-model="form.store_address" name="store_address" label="Alamat Toko" :error="form.errors.store_address" required rows="3" placeholder="cth: Jl. Merdeka No. 123, Jakarta" />
            <FormGroup name="store_phone" label="Nomor Telepon" :error="form.errors.store_phone" required>
                <input v-model="form.store_phone" type="text" name="store_phone" class="input" placeholder="cth: 021-555-0123">
            </FormGroup>
            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3">
                <input v-model="form.is_active" type="checkbox" name="is_active" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm font-semibold text-slate-700">Aktif (tampil sebagai data toko)</span>
            </label>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    {{ isEdit ? 'Simpan Perubahan' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</template>