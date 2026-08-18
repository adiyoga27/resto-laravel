<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import Pagination from '../../../Components/Pagination.vue';
import Modal from '../../../Components/Modal.vue';
import StatusBadge from '../../../Components/StatusBadge.vue';

const props = defineProps({
    categories: Object,
});

const deleting = ref(null);
const busy = ref(false);

const confirmDelete = () => {
    busy.value = true;
    router.delete(route('admin.menu-categories.destroy', deleting.value.id), {
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
        <PageHeader title="Kategori Menu" subtitle="Kelola pengelompokan menu">
            <Link :href="route('admin.menu-categories.create')" class="btn-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4" /></svg>
                Tambah Kategori
            </Link>
        </PageHeader>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="table-th">Nama</th>
                            <th class="table-th">Deskripsi</th>
                            <th class="table-th">Menu</th>
                            <th class="table-th">Urutan</th>
                            <th class="table-th">Status</th>
                            <th class="table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="category in categories.data" :key="category.id" class="transition hover:bg-slate-50">
                            <td class="table-td font-semibold text-slate-800">{{ category.name }}</td>
                            <td class="table-td max-w-xs truncate text-slate-400">{{ category.description || '—' }}</td>
                            <td class="table-td">
                                <span class="badge bg-slate-100 text-slate-700">{{ category.menu_items_count }}</span>
                            </td>
                            <td class="table-td text-slate-400">{{ category.sort_order }}</td>
                            <td class="table-td">
                                <StatusBadge :value="category.is_active ? 'active' : 'dibatalkan'">
                                    {{ category.is_active ? 'Aktif' : 'Nonaktif' }}
                                </StatusBadge>
                            </td>
                            <td class="table-td">
                                <div class="flex justify-end gap-1.5">
                                    <Link :href="route('admin.menu-categories.edit', category.id)" class="btn-ghost px-2.5 py-1.5 text-xs" title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.4a2 2 0 112.8 2.8L11 17l-4 1 1-4 9.6-9.4z" /></svg>
                                    </Link>
                                    <button class="btn-ghost px-2.5 py-1.5 text-xs text-rose-600 hover:bg-rose-50" title="Hapus" @click="deleting = category">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!categories.data.length">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <p class="text-4xl">🗂️</p>
                                <p class="mt-2 font-medium text-slate-500">Belum ada kategori</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="categories.links" :from="categories.from" :to="categories.to" :total="categories.total" />
        </div>

        <Modal
            :open="!!deleting"
            title="Hapus Kategori"
            :message="`Yakin ingin menghapus kategori ${deleting?.name}?`"
            :busy="busy"
            @close="deleting = null"
            @confirm="confirmDelete"
        />
    </div>
</template>