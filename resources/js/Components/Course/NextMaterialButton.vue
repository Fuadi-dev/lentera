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
    <Button :as-child="true" class="gap-2 px-5 py-2.5 font-bold rounded-xl text-white shadow-sm shrink-0">
        <Link :href="href" :aria-label="`Selanjutnya: ${title}`" class="flex items-center gap-2">
            <span>Selanjutnya</span>
            <PhArrowRight :size="18" weight="bold" aria-hidden="true" />
        </Link>
    </Button>
</template>
