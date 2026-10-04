<script setup>
defineProps({
    class: {
        type: String,
        default: 'size-5',
    },
});
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.75"
        stroke-linecap="round"
        stroke-linejoin="round"
        :class="['shrink-0', $props.class]"
        aria-hidden="true"
    >
        <path d="M3 12h11" />
        <path d="m10 8 4 4-4 4" />
        <path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4" />
    </svg>
</template>
