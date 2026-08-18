<script setup>
import FormGroup from './FormGroup.vue';

defineProps({
    name: { type: String, required: true },
    label: { type: String, default: '' },
    error: { type: String, default: '' },
    required: { type: Boolean, default: false },
    hint: { type: String, default: '' },
    modelValue: { type: [String, Number], default: '' },
    type: { type: String, default: 'text' },
    autocomplete: { type: String, default: 'off' },
    placeholder: { type: String, default: '' },
    step: { type: [String, Number], default: undefined },
    min: { type: [String, Number], default: undefined },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <FormGroup :name="name" :label="label" :error="error" :required="required" :hint="hint">
        <input
            :id="name"
            :name="name"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
            :step="step"
            :min="min"
            :disabled="disabled"
            class="input"
            :class="error ? 'ring-rose-300 focus:ring-rose-400' : ''"
            @input="$emit('update:modelValue', $event.target.value)"
        >
    </FormGroup>
</template>