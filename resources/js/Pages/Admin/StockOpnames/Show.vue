<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import PageHeader from '../../../Components/PageHeader.vue';
import StatusBadge from '../../../Components/StatusBadge.vue';
import Modal from '../../../Components/Modal.vue';
import { dateOnly } from '../../../utils/format';

const props = defineProps({
    stockOpname: Object,
});

const posting = ref(false);
const destroying = ref(false);
const sending = ref(false);

const isDraft = props.stockOpname.status === 'draft';

const postOpname = () => {
    sending.value = true;
    router.post(route('admin.stock-opnames.post', props.stockOpname.id), {}, {
        preserveScroll: true,
        onFinish: () => (sending.value = false),
    });
};

const destroyOpname = () => {
    sending.value = true;
    router.delete(route('admin.stock-opnames.destroy', props.stockOpname.id), {
        onFinish: () => (sending.value = false),
    });
};

const diffClass = (item) => {
    const diff = Number(item.difference);
    if (diff < 0) return 'bg-rose-50 text-rose-700';
    if (diff > 0) return 'bg-emerald-50 text-emerald-700';
    return 'bg-slate-50 text-slate-500';
};

defineOptions({ layout: AppLayout });
</script>

<template>
    <div>
        <PageHeader :title="`Stok Opname #${stockOpname.id}`" :subtitle="`Tanggal ${dateOnly(stockOpname.date)}`">
            <StatusBadge :value="stockOpname.status">{{ isDraft ? 'Draft' : 'Diposting' }}</StatusBadge>
            <button v-if="isDraft" type="button" class="btn-primary" @click="posting = true">
                <svg v-if="sending" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" /></svg>
                Posting Opname
            </button>
            <button v-if="isDraft" type="button" class="btn-danger" :disabled="sending" @click="destroying = true">
                Hapus
            </button>
        </PageHeader>

        <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="card p-4">
                <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Jumlah Item</p>
                <p class="text-xl font-bold text-slate-900">{{ stockOpname.items.length }} bahan</p>
            </div>
            <div class="card p-4">
                <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Total Selisih Positif</p>
                <p class="text-xl font-bold text-emerald-600">
                    {{ stockOpname.items.reduce((s, i) => s + Math.max(0, Number(i.difference)), 0).toLocaleString('id-ID') }}
                </p>
            </div>
            <div class="card p-4">
                <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Total Selisih Negatif</p>
                <p class="text-xl font-bold text-rose-600">
                    {{ stockOpname.items.reduce((s, i) => s + Math.min(0, Number(i.difference)), 0).toLocaleString('id-ID') }}
                </p>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="table-th">Bahan</th>
                            <th class="table-th text-right">Stok Sistem</th>
                            <th class="table-th text-right">Stok Fisik</th>
                            <th class="table-th text-right">Selisih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="item in stockOpname.items" :key="item.id" class="transition hover:bg-slate-50">
                            <td class="table-td font-medium text-slate-800">
                                {{ item.ingredient?.name }}
                                <span class="ml-1 text-xs text-slate-400">{{ item.ingredient?.unit }}</span>
                            </td>
                            <td class="table-td text-right">{{ Number(item.system_stock).toLocaleString('id-ID') }}</td>
                            <td class="table-td text-right font-semibold">{{ Number(item.actual_stock).toLocaleString('id-ID') }}</td>
                            <td class="table-td text-right">
                                <span class="badge" :class="diffClass(item)">
                                    {{ Number(item.difference).toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!stockOpname.items.length">
                            <td colspan="4" class="px-6 py-16 text-center">
                                <p class="text-4xl">🧾</p>
                                <p class="mt-2 font-medium text-slate-500">Tidak ada item</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="stockOpname.notes" class="card mt-4 p-5">
            <p class="mb-1 text-xs font-semibold tracking-wide text-slate-500 uppercase">Catatan</p>
            <p class="text-sm text-slate-700">{{ stockOpname.notes }}</p>
        </div>

        <Modal
            :open="destroying"
            title="Hapus Stok Opname"
            :message="`Yakin ingin menghapus stok opname #${stockOpname.id}?`"
            :busy="sending"
            @close="destroying = false"
            @confirm="destroyOpname"
        />
        <Modal
            :open="posting"
            title="Posting Stok Opname"
            :message="`Stok bahan baku akan disesuaikan sesuai hasil opname. Lanjutkan?`"
            confirm-text="Ya, Posting"
            confirm-class="btn-success"
            :busy="sending"
            @close="posting = false"
            @confirm="postOpname"
        />
    </div>
</template>