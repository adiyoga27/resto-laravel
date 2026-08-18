<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import Pagination from '../../Components/Pagination.vue';
import StatusBadge from '../../Components/StatusBadge.vue';
import Modal from '../../Components/Modal.vue';
import { money, dateOnly } from '../../utils/format';

const props = defineProps({
    entries: Object,
    totalDebit: Number,
    totalKredit: Number,
    saldo: Number,
    filters: Object,
});

const filterForm = useForm({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
});

const applyFilter = () => {
    filterForm.get(route('reports.cash-flow'), { preserveState: true });
};

const deleting = ref(null);
const posting = ref(null);
const busy = ref(false);

const confirmDelete = () => {
    busy.value = true;
    router.delete(route('reports.cash-flow.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
            busy.value = false;
        },
        onError: () => (busy.value = false),
    });
};

const confirmPost = () => {
    busy.value = true;
    router.post(route('reports.cash-flow.post', posting.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            posting.value = null;
            busy.value = false;
        },
        onError: () => (busy.value = false),
    });
};

const unpost = (entry) => {
    router.post(route('reports.cash-flow.unpost', entry.id), {}, { preserveScroll: true });
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div>
        <PageHeader title="Arus Kas" subtitle="Pencatatan pemasukan dan pengeluaran">
            <Link :href="route('reports.cash-flow.posting')" class="btn-warning">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                Posting Transaksi
            </Link>
            <Link :href="route('reports.cash-flow.create')" class="btn-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4" /></svg>
                Entri Baru
            </Link>
            <a
                :href="route('reports.cash-flow.export', { start_date: filterForm.start_date, end_date: filterForm.end_date })"
                class="btn-success"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Export
            </a>
        </PageHeader>

        <div class="card mb-4 p-4">
            <form class="flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="applyFilter">
                <div class="sm:w-48">
                    <label class="label">Dari Tanggal</label>
                    <input v-model="filterForm.start_date" type="date" class="input">
                </div>
                <div class="sm:w-48">
                    <label class="label">Sampai Tanggal</label>
                    <input v-model="filterForm.end_date" type="date" class="input">
                </div>
                <button type="submit" class="btn-primary">Terapkan</button>
            </form>
        </div>

        <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="card flex items-center justify-between p-5">
                <div>
                    <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Total Masuk</p>
                    <p class="text-xl font-extrabold text-emerald-600">{{ money(totalDebit) }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0l-6 6m6-6l6 6" /></svg>
                </span>
            </div>
            <div class="card flex items-center justify-between p-5">
                <div>
                    <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Total Keluar</p>
                    <p class="text-xl font-extrabold text-rose-600">{{ money(totalKredit) }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0l-6-6m6 6l6-6" /></svg>
                </span>
            </div>
            <div class="card flex items-center justify-between p-5">
                <div>
                    <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Saldo</p>
                    <p class="text-xl font-extrabold" :class="saldo >= 0 ? 'text-slate-900' : 'text-rose-600'">{{ money(saldo) }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H6.5M7 13L5.6 5M7 13l-2.3 7L4 21h1M16 11l1.5-8M9 21h10" /></svg>
                </span>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="table-th">Tanggal</th>
                            <th class="table-th">Keterangan</th>
                            <th class="table-th">Ref</th>
                            <th class="table-th">Tipe</th>
                            <th class="table-th text-right">Jumlah</th>
                            <th class="table-th">Status</th>
                            <th class="table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="entry in entries.data" :key="entry.id" class="transition hover:bg-slate-50">
                            <td class="table-td whitespace-nowrap text-slate-500">{{ dateOnly(entry.date) }}</td>
                            <td class="table-td font-medium text-slate-800">{{ entry.description }}</td>
                            <td class="table-td text-slate-400">{{ entry.reference || '-' }}</td>
                            <td class="table-td">
                                <StatusBadge :value="entry.type">{{ entry.type === 'debit' ? 'Masuk' : 'Keluar' }}</StatusBadge>
                            </td>
                            <td class="table-td text-right font-bold" :class="entry.type === 'debit' ? 'text-emerald-600' : 'text-rose-600'">
                                {{ entry.type === 'debit' ? '+' : '-' }}{{ money(entry.amount) }}
                            </td>
                            <td class="table-td">
                                <StatusBadge :value="entry.is_posted ? 'posted' : 'draft'">
                                    {{ entry.is_posted ? 'Posted' : 'Draft' }}
                                </StatusBadge>
                            </td>
                            <td class="table-td">
                                <div class="flex justify-end gap-1.5">
                                    <button v-if="!entry.is_posted" class="btn-ghost px-2.5 py-1.5 text-xs" title="Posting" @click="posting = entry">
                                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2m-6 9l2 2 4-4m-3-5V3m0 0L6 7m3-4l3 4" /></svg>
                                    </button>
                                    <Link v-if="!entry.is_posted" :href="route('reports.cash-flow.edit', entry.id)" class="btn-ghost px-2.5 py-1.5 text-xs" title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.4a2 2 0 112.8 2.8L11 17l-4 1 1-4 9.6-9.4z" /></svg>
                                    </Link>
                                    <button v-if="!entry.is_posted" class="btn-ghost px-2.5 py-1.5 text-xs text-rose-600 hover:bg-rose-50" title="Hapus" @click="deleting = entry">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                    <button v-if="entry.is_posted" class="btn-ghost px-2.5 py-1.5 text-xs" title="Unpost" @click="unpost(entry)">
                                        <svg class="h-4 w-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636M3 3l18 18" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!entries.data.length">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <p class="text-4xl">💰</p>
                                <p class="mt-2 font-medium text-slate-500">Belum ada entri arus kas</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="entries.links" :from="entries.from" :to="entries.to" :total="entries.total" />
        </div>

        <Modal
            :open="!!deleting"
            title="Hapus Entri"
            :message="`Yakin ingin menghapus entri ini? (${deleting?.description})`"
            :busy="busy"
            @close="deleting = null"
            @confirm="confirmDelete"
        />
        <Modal
            :open="!!posting"
            title="Posting Entri"
            :message="`Posting entri ini? (${posting?.description})`"
            confirm-text="Ya, Posting"
            confirm-class="btn-success"
            :busy="busy"
            @close="posting = null"
            @confirm="confirmPost"
        />
    </div>
</template>