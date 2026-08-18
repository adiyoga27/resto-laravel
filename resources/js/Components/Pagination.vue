<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    links: {
        type: Array,
        default: () => [],
    },
    from: {
        type: Number,
        default: null,
    },
    to: {
        type: Number,
        default: null,
    },
    total: {
        type: Number,
        default: 0,
    },
});
</script>

<template>
    <div v-if="links.length > 3" class="flex items-center justify-between gap-4 border-t border-slate-200 px-4 py-3">
        <p class="text-xs text-slate-500">
            Menampilkan <span class="font-semibold text-slate-700">{{ from ?? 0 }}</span>–<span class="font-semibold text-slate-700">{{ to ?? 0 }}</span> dari <span class="font-semibold text-slate-700">{{ total }}</span>
        </p>
        <nav class="flex flex-wrap items-center gap-1">
            <component
                :is="link.url ? Link : 'span'"
                v-for="(link, i) in links"
                :key="i"
                :href="link.url"
                :class="[
                    'inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-medium transition',
                    link.active ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100',
                    !link.url && 'pointer-events-none text-slate-300',
                ]"
                v-html="link.label"
            />
        </nav>
    </div>
</template>