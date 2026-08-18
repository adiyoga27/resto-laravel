<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import Pagination from '../../../Components/Pagination.vue';
import Modal from '../../../Components/Modal.vue';
import StatusBadge from '../../../Components/StatusBadge.vue';

const props = defineProps({
    users: Object,
});

const deleting = ref(null);
const busy = ref(false);

const askDelete = (user) => {
    deleting.value = user;
};

const confirmDelete = () => {
    busy.value = true;
    router.delete(route('admin.users.destroy', deleting.value.id), {
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
        <PageHeader title="Manajemen User" subtitle="Kelola akun pengguna aplikasi">
            <Link :href="route('admin.users.create')" class="btn-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4" /></svg>
                Tambah User
            </Link>
        </PageHeader>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="table-th">Nama</th>
                            <th class="table-th">Email</th>
                            <th class="table-th">Role</th>
                            <th class="table-th">Status</th>
                            <th class="table-th">Dibuat</th>
                            <th class="table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="user in users.data" :key="user.id" class="transition hover:bg-slate-50">
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-slate-500 to-slate-700 text-sm font-bold text-white">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ user.name }}</p>
                                        <p v-if="user.phone" class="text-xs text-slate-400">{{ user.phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td">{{ user.email }}</td>
                            <td class="table-td">
                                <span class="badge bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200 capitalize">{{ user.role }}</span>
                            </td>
                            <td class="table-td">
                                <StatusBadge :value="user.is_active ? 'active' : 'dibatalkan'">
                                    {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                </StatusBadge>
                            </td>
                            <td class="table-td text-slate-400">{{ new Date(user.created_at).toLocaleDateString('id-ID') }}</td>
                            <td class="table-td">
                                <div class="flex justify-end gap-1.5">
                                    <Link :href="route('admin.users.edit', user.id)" class="btn-ghost px-2.5 py-1.5 text-xs" title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.4a2 2 0 112.8 2.8L11 17l-4 1 1-4 9.6-9.4z" /></svg>
                                    </Link>
                                    <button class="btn-ghost px-2.5 py-1.5 text-xs text-rose-600 hover:bg-rose-50" title="Hapus" @click="askDelete(user)">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!users.data.length">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <p class="text-4xl">🕖</p>
                                <p class="mt-2 font-medium text-slate-500">Belum ada user</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="users.links" :from="users.from" :to="users.to" :total="users.total" />
        </div>

        <Modal
            :open="!!deleting"
            title="Hapus User"
            :message="`Yakin ingin menghapus user ${deleting?.name}? Tindakan ini tidak bisa dibatalkan.`"
            :busy="busy"
            @close="deleting = null"
            @confirm="confirmDelete"
        />
    </div>
</template>