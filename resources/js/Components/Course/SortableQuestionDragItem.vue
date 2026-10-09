<script setup>
import { computed, ref } from 'vue';
import { useSortable } from '@dnd-kit/vue/sortable';

const props = defineProps({
    id: { type: [String, Number], required: true },
    questionId: { type: [String, Number], required: true },
    itemId: { type: [String, Number], required: true },
    index: { type: Number, required: true },
    label: { type: String, required: true },
    image: { type: String, default: null },
});

const element = ref(null);

const { isDragging } = useSortable({
    id: computed(() => props.id),
    index: computed(() => props.index),
    group: computed(() => `question-${props.questionId}`),
    data: computed(() => ({
        questionId: props.questionId,
        itemId: props.itemId,
        isPlaced: true,
    })),
    element,
});
</script>

<template>
    <button
        ref="element"
        type="button"
        class="flex flex-col items-center justify-center gap-1 p-2 text-sm transition border rounded-lg shadow-sm min-h-16 min-w-20 touch-none border-border bg-background hover:border-primary hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        :class="{ 'opacity-40': isDragging }"
        :aria-label="`Urutkan ${label}`"
    >
        <img v-if="image" :src="image" :alt="label" class="object-contain size-10" />
        <span class="truncate max-w-28">{{ label }}</span>
    </button>
</template>
