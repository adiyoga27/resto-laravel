<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import FormGroup from '../../../Components/FormGroup.vue';
import FormToggle from '../../../Components/FormToggle.vue';

const props = defineProps({
    menuItem: Object,
    categories: Array,
});

const form = useForm({
    menu_category_id: props.menuItem.menu_category_id,
    name: props.menuItem.name,
    description: props.menuItem.description ?? '',
    price: props.menuItem.price,
    stock: props.menuItem.stock,
    sort_order: props.menuItem.sort_order,
    is_active: props.menuItem.is_active,
    image: null,
});

const previewUrl = ref(props.menuItem.image ? `/storage/${props.menuItem.image}` : null);

const onImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        previewUrl.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.put(route('admin.menu-items.update', props.menuItem.id), {
        forceFormData: true,
    });
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div class="mx-auto max-w-2xl">
        <PageHeader title="Edit Menu" :subtitle="`Memperbarui ${menuItem.name}`" />
        <form class="card space-y-5 p-6" @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <FormGroup name="menu_category_id" label="Kategori" :error="form.errors.menu_category_id" required>
                    <select v-model="form.menu_category_id" name="menu_category_id" class="input">
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                </FormGroup>
                <FormGroup name="name" label="Nama Menu" :error="form.errors.name" required>
                    <input v-model="form.name" type="text" name="name" class="input">
                </FormGroup>
            </div>
            <FormGroup name="description" label="Deskripsi" :error="form.errors.description">
                <textarea v-model="form.description" name="description" rows="3" class="input" />
            </FormGroup>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <FormGroup name="price" label="Harga (Rp)" :error="form.errors.price" required>
                    <input v-model="form.price" type="number" name="price" min="0" step="0.01" class="input">
                </FormGroup>
                <FormGroup name="stock" label="Stok" :error="form.errors.stock">
                    <input v-model.number="form.stock" type="number" name="stock" min="0" class="input">
                </FormGroup>
                <FormGroup name="sort_order" label="Urutan" :error="form.errors.sort_order">
                    <input v-model.number="form.sort_order" type="number" name="sort_order" min="0" class="input">
                </FormGroup>
            </div>
            <FormGroup name="image" label="Gambar Menu" :error="form.errors.image" hint="Upload gambar baru untuk menggantikan yang lama (maks 2MB)">
                <div class="flex items-center gap-4">
                    <img v-if="previewUrl" :src="previewUrl" class="h-20 w-20 rounded-xl object-cover ring-1 ring-slate-200">
                    <div v-else class="flex h-20 w-20 items-center justify-center rounded-xl bg-slate-100 text-2xl">🍽️</div>
                    <label class="btn-secondary cursor-pointer">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        Pilih Gambar
                        <input type="file" name="image" accept="image/*" class="hidden" @change="onImageChange">
                    </label>
                </div>
            </FormGroup>
            <FormToggle v-model="form.is_active" name="is_active" label="Menu Aktif" />

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a :href="route('admin.menu-items.index')" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</template>