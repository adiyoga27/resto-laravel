<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import FormGroup from '../../../Components/FormGroup.vue';
import Modal from '../../../Components/Modal.vue';

const props = defineProps({
    menuItems: Array,
    ingredients: Array,
    recipes: Object,
    menuItemId: [String, Number, null],
});

const form = useForm({
    menu_item_id: '',
    ingredient_id: '',
    quantity: '',
});

const deleting = ref(null);
const busy = ref(false);

const groupedRecipes = computed(() => {
    return Object.entries(props.recipes ?? {}).map(([menuId, items]) => ({
        menuId: Number(menuId),
        items: items.map((item) => {
            const ingredient = props.ingredients.find((i) => i.id === item.ingredient_id);
            const menuItem = props.menuItems.find((m) => m.id === item.menu_item_id);
            return { ...item, ingredient, menuItem };
        }),
    }));
});

const selectedMenu = computed(() => props.menuItems.find((m) => String(m.id) === String(props.menuItemId)));

const submit = () => {
    form.post(route('admin.recipes.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset('quantity'),
    });
};

const filterBy = (menuId) => {
    router.get(route('admin.recipes.index', menuId ? { menu_item_id: menuId } : {}), {}, { preserveState: true });
};

const editRecipe = (item) => {
    form.menu_item_id = item.menu_item_id;
    form.ingredient_id = item.ingredient_id;
    form.quantity = item.quantity;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const confirmDelete = () => {
    busy.value = true;
    router.delete(route('admin.recipes.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
            busy.value = false;
        },
        onError: () => (busy.value = false),
    });
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div>
        <PageHeader
            :title="selectedMenu ? `Resep — ${selectedMenu.name}` : 'Resep Menu (BOM)'"
            subtitle="Atur komposisi bahan baku untuk setiap menu"
        />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Recipe list -->
            <div class="space-y-4 lg:col-span-2">
                <div class="card p-4">
                    <label class="label">Filter Menu</label>
                    <select class="input" :value="menuItemId ?? ''" @change="filterBy($event.target.value)">
                        <option value="">-- Semua Menu --</option>
                        <option v-for="menu in menuItems" :key="menu.id" :value="menu.id">
                            {{ menu.name }} ({{ menu.category?.name ?? '-' }})
                        </option>
                    </select>
                </div>

                <div v-if="!groupedRecipes.length" class="card p-10 text-center">
                    <p class="text-4xl">📖</p>
                    <p class="mt-3 font-medium text-slate-500">Belum ada resep. Tambahkan menggunakan form di samping.</p>
                </div>

                <div v-for="group in groupedRecipes" :key="group.menuId" class="card overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-3">
                        <h3 class="font-semibold text-slate-800">
                            {{ group.items[0].menuItem?.name }}
                            <span class="ml-1 text-xs font-normal text-slate-400">{{ group.items[0].menuItem?.category?.name }}</span>
                        </h3>
                        <span class="badge bg-indigo-50 text-indigo-700">{{ group.items.length }} bahan</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="border-b border-slate-100">
                                <tr>
                                    <th class="table-th">Bahan</th>
                                    <th class="table-th">Satuan</th>
                                    <th class="table-th text-right">Qty per Porsi</th>
                                    <th class="table-th text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in group.items" :key="item.id" class="transition hover:bg-slate-50">
                                    <td class="table-td font-medium text-slate-800">{{ item.ingredient?.name }}</td>
                                    <td class="table-td text-slate-400">{{ item.ingredient?.unit }}</td>
                                    <td class="table-td text-right font-semibold">{{ Number(item.quantity).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}</td>
                                    <td class="table-td">
                                        <div class="flex justify-end gap-1.5">
                                            <button class="btn-ghost px-2.5 py-1.5 text-xs" title="Edit" @click="editRecipe(item)">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.4a2 2 0 112.8 2.8L11 17l-4 1 1-4 9.6-9.4z" /></svg>
                                            </button>
                                            <button class="btn-ghost px-2.5 py-1.5 text-xs text-rose-600 hover:bg-rose-50" title="Hapus" @click="deleting = item">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add/Edit form -->
            <div class="card h-fit p-5" id="recipeForm">
                <h3 class="mb-4 font-bold text-slate-900">Tambah / Edit Resep</h3>
                <form class="space-y-4" @submit.prevent="submit">
                    <FormGroup name="recipe_menu_item_id" label="Menu" :error="form.errors.menu_item_id" required>
                        <select v-model="form.menu_item_id" name="menu_item_id" class="input">
                            <option value="">Pilih Menu</option>
                            <option v-for="menu in menuItems" :key="menu.id" :value="menu.id">{{ menu.name }} ({{ menu.category?.name ?? '-' }})</option>
                        </select>
                    </FormGroup>
                    <FormGroup name="recipe_ingredient_id" label="Bahan Baku" :error="form.errors.ingredient_id" required>
                        <select v-model="form.ingredient_id" name="ingredient_id" class="input">
                            <option value="">Pilih Bahan</option>
                            <option v-for="ingredient in ingredients" :key="ingredient.id" :value="ingredient.id">{{ ingredient.name }} ({{ ingredient.unit }})</option>
                        </select>
                    </FormGroup>
                    <FormGroup name="quantity" label="Qty per 1 Porsi" :error="form.errors.quantity" required>
                        <input v-model="form.quantity" type="number" name="quantity" min="0.01" step="0.01" class="input" placeholder="0.00">
                    </FormGroup>
                    <button type="submit" class="btn-primary w-full" :disabled="form.processing">
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                        Simpan Resep
                    </button>
                </form>
            </div>
        </div>

        <Modal
            :open="!!deleting"
            title="Hapus Item Resep"
            message="Yakin ingin menghapus item resep ini?"
            :busy="busy"
            @close="deleting = null"
            @confirm="confirmDelete"
        />
    </div>
</template>