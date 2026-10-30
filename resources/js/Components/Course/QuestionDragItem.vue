<script setup>
import { ref } from 'vue';
import { useDraggable } from '@dnd-kit/vue';

const props = defineProps({
    id: { type: [String, Number], required: true },
    questionId: { type: [String, Number], required: true },
    itemId: { type: [String, Number], default: null },
    label: { type: String, required: true },
    image: { type: String, default: null },
});

const element = ref(null);

const { draggable, isDragging } = useDraggable({
    id: `question-item-${props.questionId}-${props.id}`,
    data: {
        questionId: props.questionId,
        itemId: props.itemId ?? props.id,
        isPlaced: props.itemId !== null,
    },
    element,
});
</script>

<template>
    <button
        ref="element"
        type="button"
        class="flex flex-col items-center justify-center gap-1 p-2 text-sm transition border rounded-lg shadow-sm min-h-16 min-w-20 touch-none border-border bg-background hover:border-primary hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        :class="{ 'opacity-40': isDragging }"
        :aria-label="`Seret ${label}`"
    >
        <img v-if="image" :src="image" :alt="label" class="object-contain size-10" />
        <span class="text-center break-words w-full">{{ label }}</span>
    </button>
</template>
