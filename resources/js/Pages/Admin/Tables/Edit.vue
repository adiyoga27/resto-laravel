<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import FormGroup from '../../../Components/FormGroup.vue';

const props = defineProps({
    table: Object,
    statuses: Array,
});

const form = useForm({
    table_number: props.table.table_number,
    capacity: props.table.capacity,
    status: props.table.status,
});

const submit = () => {
    form.put(route('admin.tables.update', props.table.id));
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="mx-auto max-w-xl">
        <PageHeader title="Edit Meja" :subtitle="`Memperbarui meja ${table.table_number}`" />
        <form class="card space-y-5 p-6" @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <FormGroup name="table_number" label="Nomor Meja" :error="form.errors.table_number" required>
                    <input v-model="form.table_number" type="text" name="table_number" class="input">
                </FormGroup>
                <FormGroup name="capacity" label="Kapasitas (kursi)" :error="form.errors.capacity" required>
                    <input v-model.number="form.capacity" type="number" name="capacity" min="1" class="input">
                </FormGroup>
            </div>
            <FormGroup name="status" label="Status" :error="form.errors.status" required>
                <select v-model="form.status" name="status" class="input">
                    <option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                </select>
            </FormGroup>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a :href="route('admin.tables.index')" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</template>