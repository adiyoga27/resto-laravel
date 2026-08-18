<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import Pagination from '../../../Components/Pagination.vue';
import Modal from '../../../Components/Modal.vue';
import StatusBadge from '../../../Components/StatusBadge.vue';
import { dateOnly } from '../../../utils/format';

const props = defineProps({
    opnames: Object,
});

const deleting = ref(null);
const busy = ref(false);

const confirmDelete = () => {
    busy.value = true;
    router.delete(route('admin.stock-opnames.destroy', deleting.value.id), {
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
        <PageHeader title="Stok Opname" subtitle="Penghitungan stok fisik bahan baku">
            <Link :href="route('admin.stock-opnames.create')" class="btn-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4" /></svg>
                Buat Opname
            </Link>
        </PageHeader>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="table-th">ID</th>
                            <th class="table-th">Tanggal</th>
                            <th class="table-th">Catatan</th>
                            <th class="table-th">Status</th>
                            <th class="table-th">Oleh</th>
                            <th class="table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="opname in opnames.data" :key="opname.id" class="transition hover:bg-slate-50">
                            <td class="table-td font-semibold text-slate-800">#{{ opname.id }}</td>
                            <td class="table-td whitespace-nowrap">{{ dateOnly(opname.date) }}</td>
                            <td class="table-td max-w-xs truncate text-slate-400">{{ opname.notes || '—' }}</td>
                            <td class="table-td">
                                <StatusBadge :value="opname.status">{{ opname.status === 'draft' ? 'Draft' : 'Diposting' }}</StatusBadge>
                            </td>
                            <td class="table-td text-slate-400">{{ opname.user?.name ?? '—' }}</td>
                            <td class="table-td">
                                <div class="flex justify-end gap-1.5">
                                    <Link :href="route('admin.stock-opnames.show', opname.id)" class="btn-ghost px-2.5 py-1.5 text-xs">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </Link>
                                    <button v-if="opname.status === 'draft'" class="btn-ghost px-2.5 py-1.5 text-xs text-rose-600 hover:bg-rose-50" title="Hapus" @click="deleting = opname">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!opnames.data.length">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <p class="text-4xl">🧾</p>
                                <p class="mt-2 font-medium text-slate-500">Belum ada stok opname</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="opnames.links" :from="opnames.from" :to="opnames.to" :total="opnames.total" />
        </div>

        <Modal
            :open="!!deleting"
            title="Hapus Stok Opname"
            :message="`Yakin ingin menghapus stok opname #${deleting?.id}?`"
            :busy="busy"
            @close="deleting = null"
            @confirm="confirmDelete"
        />
    </div>
</template>