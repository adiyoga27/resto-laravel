<script setup>
import FlashMessages from '../Components/FlashMessages.vue';

const props = defineProps({
    title: String,
    subtitle: String,
    appName: String,
});
</script>

<template>
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-950 px-4 py-12">
        <div class="pointer-events-none absolute inset-0 select-none">
            <div class="absolute -top-40 -left-40 h-96 w-96 rounded-full bg-indigo-600/30 blur-3xl" />
            <div class="absolute -right-32 -bottom-40 h-96 w-96 rounded-full bg-fuchsia-600/20 blur-3xl" />
            <div class="absolute top-1/3 right-1/4 h-64 w-64 rounded-full bg-sky-500/20 blur-3xl" />
        </div>

        <FlashMessages />

        <div class="relative w-full max-w-md">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-3xl shadow-2xl shadow-indigo-900/50 ring-1 ring-white/10">
                    🍽️
                </div>
                <h1 class="text-2xl font-bold text-white">{{ title }}</h1>
                <p class="mt-1 text-sm text-slate-400">{{ subtitle }}</p>
            </div>

            <div class="rounded-2xl bg-white/95 p-8 shadow-2xl ring-1 ring-white/20 backdrop-blur">
                <slot />
            </div>

            <p class="mt-6 text-center text-xs text-slate-500">© {{ new Date().getFullYear() }} {{ appName }} — Point of Sale</p>
        </div>
    </div>
</template>