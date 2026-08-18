<script setup>
import FormGroup from './FormGroup.vue';

defineProps({
    name: { type: String, required: true },
    label: { type: String, default: '' },
    error: { type: String, default: '' },
    modelValue: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <FormGroup :name="name" :label="label" :error="error">
        <button
            type="button"
            :id="name"
            role="switch"
            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            :class="modelValue ? 'bg-indigo-600' : 'bg-slate-300'"
            :aria-checked="modelValue"
            @click="$emit('update:modelValue', !modelValue)"
        >
            <span
                class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
                :class="modelValue ? 'translate-x-6' : 'translate-x-1'"
            />
        </button>
    </FormGroup>
</template>