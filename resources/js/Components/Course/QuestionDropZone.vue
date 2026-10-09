<script setup>
import { ref } from 'vue';
import { useDroppable } from '@dnd-kit/vue';

const props = defineProps({
    id: { type: [String, Number], required: true },
    questionId: { type: [String, Number], required: true },
    label: { type: String, required: true },
});

const element = ref(null);

const { droppable, isDropTarget } = useDroppable({
    id: `question-zone-${props.questionId}-${props.id}`,
    data: {
        questionId: props.questionId,
        zoneId: props.id,
    },
    element,
});
</script>

<template>
    <div
        ref="element"
        class="p-3 transition border-2 border-dashed rounded-lg min-h-28 border-border bg-muted/30"
        :class="{ 'border-primary bg-primary/10': isDropTarget }"
        :aria-label="`Tempat jawaban ${label}`"
    >
        <p class="mb-2 text-sm font-medium text-muted-foreground">{{ label }}</p>
        <div class="flex flex-wrap items-center gap-2 min-h-16">
            <slot />
        </div>
    </div>
</template>
