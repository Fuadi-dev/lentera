<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    class: {
        type: [String, Object, Array],
        required: false,
    },
});
</script>

<template>
    <article
        :class="
            cn(
                'rounded-lg border border-border bg-card text-card-foreground shadow-sm',
                props.class
            )
        "
    >
        <slot />
    </article>
</template>
