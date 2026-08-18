<script setup>
import FormGroup from './FormGroup.vue';

defineProps({
    name: { type: String, required: true },
    label: { type: String, default: '' },
    error: { type: String, default: '' },
    required: { type: Boolean, default: false },
    hint: { type: String, default: '' },
    modelValue: { type: [String, Number], default: '' },
    placeholder: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <FormGroup :name="name" :label="label" :error="error" :required="required" :hint="hint">
        <select
            :id="name"
            :name="name"
            :value="modelValue"
            class="input"
            :class="error ? 'ring-rose-300 focus:ring-rose-400' : ''"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option v-if="placeholder" value="">{{ placeholder }}</option>
            <slot />
        </select>
    </FormGroup>
</template>