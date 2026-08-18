<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import Pagination from '../../../Components/Pagination.vue';
import Modal from '../../../Components/Modal.vue';
import StatusBadge from '../../../Components/StatusBadge.vue';

const props = defineProps({
    ingredients: Object,
});

const deleting = ref(null);
const busy = ref(false);

const confirmDelete = () => {
    busy.value = true;
    router.delete(route('admin.ingredients.destroy', deleting.value.id), {
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
        <PageHeader title="Bahan Baku" subtitle="Kelola inventori bahan baku">
            <Link :href="route('admin.ingredients.create')" class="btn-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4" /></svg>
                Tambah Bahan
            </Link>
        </PageHeader>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="table-th">Nama</th>
                            <th class="table-th">Satuan</th>
                            <th class="table-th text-right">Stok Saat Ini</th>
                            <th class="table-th text-right">Stok Minimum</th>
                            <th class="table-th text-right">Harga (Rp)</th>
                            <th class="table-th">Status</th>
                            <th class="table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="ingredient in ingredients.data" :key="ingredient.id" class="transition hover:bg-slate-50">
                            <td class="table-td font-semibold text-slate-800">{{ ingredient.name }}</td>
                            <td class="table-td">{{ ingredient.unit }}</td>
                            <td class="table-td text-right">
                                <span class="badge" :class="Number(ingredient.current_stock) <= Number(ingredient.min_stock) ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700'">
                                    {{ Number(ingredient.current_stock).toLocaleString('id-ID') }} {{ ingredient.unit }}
                                </span>
                            </td>
                            <td class="table-td text-right">{{ Number(ingredient.min_stock).toLocaleString('id-ID') }}</td>
                            <td class="table-td text-right">{{ Number(ingredient.cost_price).toLocaleString('id-ID') }}</td>
                            <td class="table-td">
                                <StatusBadge :value="ingredient.is_active ? 'active' : 'dibatalkan'">
                                    {{ ingredient.is_active ? 'Aktif' : 'Nonaktif' }}
                                </StatusBadge>
                            </td>
                            <td class="table-td">
                                <div class="flex justify-end gap-1.5">
                                    <Link :href="route('admin.ingredients.edit', ingredient.id)" class="btn-ghost px-2.5 py-1.5 text-xs" title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.4a2 2 0 112.8 2.8L11 17l-4 1 1-4 9.6-9.4z" /></svg>
                                    </Link>
                                    <button class="btn-ghost px-2.5 py-1.5 text-xs text-rose-600 hover:bg-rose-50" title="Hapus" @click="deleting = ingredient">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!ingredients.data.length">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <p class="text-4xl">🧺</p>
                                <p class="mt-2 font-medium text-slate-500">Belum ada bahan baku</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="ingredients.links" :from="ingredients.from" :to="ingredients.to" :total="ingredients.total" />
        </div>

        <Modal
            :open="!!deleting"
            title="Hapus Bahan"
            :message="`Yakin ingin menghapus ${deleting?.name}?`"
            :busy="busy"
            @close="deleting = null"
            @confirm="confirmDelete"
        />
    </div>
</template>