<script setup>
import FormGroup from './FormGroup.vue';

defineProps({
    name: { type: String, required: true },
    label: { type: String, default: '' },
    error: { type: String, default: '' },
    required: { type: Boolean, default: false },
    hint: { type: String, default: '' },
    modelValue: { type: String, default: '' },
    rows: { type: Number, default: 3 },
    placeholder: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <FormGroup :name="name" :label="label" :error="error" :required="required" :hint="hint">
        <textarea
            :id="name"
            :name="name"
            :rows="rows"
            :value="modelValue"
            :placeholder="placeholder"
            class="input"
            :class="error ? 'ring-rose-300 focus:ring-rose-400' : ''"
            @input="$emit('update:modelValue', $event.target.value)"
        />
    </FormGroup>
</template>