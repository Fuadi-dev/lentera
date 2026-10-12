<script setup>
import { Link } from '@inertiajs/vue3';
import { PhArrowRight } from '@phosphor-icons/vue';
import Button from '@/Components/ui/button/Button.vue';

defineProps({
    href: { type: String, required: true },
    title: { type: String, required: true },
});
</script>

<template>
    <Button :as-child="true" size="lg" class="px-6 py-3 text-white">
        <Link :href="href" :aria-label="`Selanjutnya: ${title}`">
            <span>Selanjutnya</span>
            <PhArrowRight :size="18" aria-hidden="true" />
        </Link>
    </Button>
</template>
