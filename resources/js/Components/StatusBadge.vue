<script setup>
import { computed } from 'vue';

const props = defineProps({
    value: { type: String, default: '' },
});

const map = {
    baru: 'bg-sky-50 text-sky-700 ring-sky-200',
    diproses: 'bg-amber-50 text-amber-700 ring-amber-200',
    siap: 'bg-violet-50 text-violet-700 ring-violet-200',
    selesai: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    dibatalkan: 'bg-rose-50 text-rose-700 ring-rose-200',
    kosong: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    terisi: 'bg-amber-50 text-amber-700 ring-amber-200',
    direservasi: 'bg-sky-50 text-sky-700 ring-sky-200',
    draft: 'bg-slate-50 text-slate-600 ring-slate-200',
    posted: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    active: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    debit: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    kredit: 'bg-rose-50 text-rose-700 ring-rose-200',
    in: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    out: 'bg-rose-50 text-rose-700 ring-rose-200',
    production: 'bg-violet-50 text-violet-700 ring-violet-200',
    adjustment: 'bg-amber-50 text-amber-700 ring-amber-200',
    opname: 'bg-sky-50 text-sky-700 ring-sky-200',
};

const classes = computed(() => map[props.value] ?? 'bg-slate-50 text-slate-600 ring-slate-200');
</script>

<template>
    <span class="badge ring-1 ring-inset" :class="classes">
        <slot />
    </span>
</template>