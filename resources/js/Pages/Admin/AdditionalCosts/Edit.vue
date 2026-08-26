<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import FormGroup from '../../../Components/FormGroup.vue';

const props = defineProps({
    cost: Object,
});

const form = useForm({
    name: props.cost.name,
    rate: Number(props.cost.rate),
    is_active: props.cost.is_active,
    sort_order: props.cost.sort_order,
});

const submit = () => {
    form.put(route('admin.additional-costs.update', props.cost.id));
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="mx-auto max-w-xl">
        <PageHeader title="Edit Biaya Tambahan" :subtitle="`Memperbarui ${cost.name}`" />
        <form class="card space-y-5 p-6" @submit.prevent="submit">
            <FormGroup name="name" label="Nama" :error="form.errors.name" required>
                <input v-model="form.name" type="text" name="name" class="input">
            </FormGroup>
            <FormGroup name="rate" label="Persentase (%)" :error="form.errors.rate" required>
                <input v-model.number="form.rate" type="number" name="rate" min="0" max="100" step="0.01" class="input">
            </FormGroup>
            <FormGroup name="sort_order" label="Urutan" :error="form.errors.sort_order">
                <input v-model.number="form.sort_order" type="number" name="sort_order" min="0" class="input">
            </FormGroup>
            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3">
                <input v-model="form.is_active" type="checkbox" name="is_active" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm font-semibold text-slate-700">Aktif (tampil di POS)</span>
            </label>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a :href="route('admin.additional-costs.index')" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</template>