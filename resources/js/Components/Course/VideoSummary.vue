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
    source: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <section class="px-6 pb-6" aria-labelledby="video-summary-heading">
        <h3 id="video-summary-heading" class="text-xl font-semibold text-foreground">
            {{ title }}
        </h3>
        <p class="mt-3 text-base leading-8 text-justify whitespace-pre-line text-muted-foreground">
            {{ summary }}
        </p>
        <p v-if="source" class="mt-4 text-sm text-muted-foreground">
            Sumber:
            <a
                :href="source"
                target="_blank"
                rel="noopener noreferrer"
                class="font-medium text-primary hover:underline"
            >
                YouTube
            </a>
        </p>
    </section>
</template>
