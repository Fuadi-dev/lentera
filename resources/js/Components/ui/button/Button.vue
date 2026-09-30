<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    class: {
        type: [String, Object, Array],
        required: false,
    },
    variant: {
        type: String,
        default: 'default',
    },
});
</script>

<template>
    <button
        :class="
            cn(
                'inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-md px-4 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
                {
                    'bg-primary text-primary-foreground hover:bg-primary/90': variant === 'default',
                    'border border-input bg-background hover:bg-muted': variant === 'outline',
                    'text-foreground hover:bg-muted': variant === 'ghost',
                },
                props.class
            )
        "
    >
        <slot />
    </button>
</template>
