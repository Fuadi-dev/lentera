<script setup>
defineProps({
    summary: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: 'Rangkuman Video',
    },
});
</script>

<template>
    <section class="px-6 pb-6" aria-labelledby="video-summary-heading">
        <h3 id="video-summary-heading" class="text-xl font-semibold text-foreground">
            {{ title }}
        </h3>
        <p class="mt-3 text-base leading-8 whitespace-pre-line text-muted-foreground">
            {{ summary }}
        </p>
    </section>
</template>
