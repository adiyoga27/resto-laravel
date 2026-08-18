<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage();
const visible = ref([]);

const success = computed(() => page.props.flash?.success);
const error = computed(() => page.props.flash?.error);
const errors = computed(() => Object.values(page.props.errors ?? {}));

const meals = [
    { type: 'success', text: success.value, color: 'emerald' },
    { type: 'error', text: error.value, color: 'rose' },
    ...errors.value.map((text) => ({ type: 'error', text, color: 'rose' })),
].filter((m) => m.text);

watch(meals, (next) => {
    visible.value = next;
    next.forEach((note, index) => {
        setTimeout(() => removeAt(index), 5000);
    });
});

const removeAt = (index) => {
    visible.value.splice(index, 1);
};
</script>

<template>
    <div class="pointer-events-none fixed top-20 right-4 z-[60] flex w-96 max-w-[calc(100vw-2rem)] flex-col gap-3 sm:right-6">
        <TransitionGroup name="flash">
            <div
                v-for="(note, index) in visible"
                :key="note.text + index"
                class="pointer-events-auto flex items-start gap-3 rounded-xl p-4 shadow-lg ring-1 backdrop-blur"
                :class="note.color === 'emerald' ? 'bg-emerald-50 text-emerald-900 ring-emerald-200' : 'bg-rose-50 text-rose-900 ring-rose-200'"
            >
                <div
                    class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-white"
                    :class="note.color === 'emerald' ? 'bg-emerald-500' : 'bg-rose-500'"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path v-if="note.color === 'emerald'" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <p class="flex-1 text-sm font-medium">{{ note.text }}</p>
                <button class="text-slate-400 hover:text-slate-600" @click="removeAt(index)">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.flash-enter-active,
.flash-leave-active {
    transition: all 0.3s ease;
}
.flash-enter-from,
.flash-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(0.97);
}
</style>