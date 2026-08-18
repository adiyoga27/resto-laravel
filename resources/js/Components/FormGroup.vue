<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, required: true },
    label: { type: String, default: '' },
    error: { type: String, default: '' },
    required: { type: Boolean, default: false },
    hint: { type: String, default: '' },
});
</script>

<template>
    <div>
        <label v-if="label" :for="name" class="label">
            {{ label }}
            <span v-if="required" class="text-rose-500">*</span>
        </label>
        <slot />
        <p v-if="hint && !error" class="mt-1 text-xs text-slate-400">{{ hint }}</p>
        <p v-if="error" class="mt-1 text-xs font-medium text-rose-600">{{ error }}</p>
    </div>
</template>