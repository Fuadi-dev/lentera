<script setup lang="ts">
import { useDraggable, useDroppable } from '@dnd-kit/vue';
import { ref } from 'vue';

const dragElement = ref<HTMLElement | null>(null);
const { isDragging } = useDraggable({
    id: 'draggable-element',
    element: dragElement,
});

const dropElement = ref<HTMLElement | null>(null);
useDroppable({
    id: 'droppable-element',
    element: dropElement,
});
</script>

<template>
    <div ref="dragElement" :style="{ opacity: isDragging ? 0.5 : 1 }">Drag me</div>

    <div
        ref="dropElement"
        style="width: 200px; height: 200px; border: 1px solid black; margin-top: 20px"
    >
        Drop here
    </div>
</template>
