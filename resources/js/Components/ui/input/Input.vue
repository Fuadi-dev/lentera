<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    class: {
        type: [String, Object, Array],
        required: false,
    },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <input
        :value="modelValue"
        @input="emit('update:modelValue', $event.target.value)"
        :class="
            cn(
                'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50',
                props.class
            )
        "
    />
</template>
