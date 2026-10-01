<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { PhArrowLeft, PhBookOpen, PhQuestion, PhVideoCamera } from '@phosphor-icons/vue';
import '@vue-flow/core/dist/style.css';
import '@vue-flow/core/dist/theme-default.css';
import { Handle, Position, VueFlow } from '@vue-flow/core';

const props = defineProps({
    course: {
        type: Object,
        required: true,
    },
    classNumber: {
        type: [Number, String],
        required: true,
    },
});

const selectedClass = computed(() =>
    props.course.classes?.find((courseClass) => courseClass.number === Number(props.classNumber))
);

const learningPath = computed(
    () =>
        selectedClass.value?.section?.flatMap((section) =>
            section.unit.map((material) => ({
                ...material,
                sectionTitle: section.title,
            }))
        ) ?? []
);

const flowHeight = computed(() => {
    const groupCount = Math.ceil(learningPath.value.length / 3);
    const groupGap = Math.max(0, groupCount - 1) * 56;

    return `${Math.max(544, learningPath.value.length * 140 + groupGap + 120)}px`;
});

function flowPosition(index) {
    const positionInGroup = index % 3;
    const group = Math.floor(index / 3);
    const isMovingRight = group % 2 === 0;

    return {
        x: (isMovingRight ? positionInGroup : 2 - positionInGroup) * 280,
        y: index * 140 + group * 56,
    };
}

const flowNodes = computed(() =>
    learningPath.value.map((material, index) => {
        return {
            id: `material-${material.id ?? index}`,
            type: 'learningMaterial',
            position: flowPosition(index),
            data: { material, index },
        };
    })
);

const flowEdges = computed(() =>
    learningPath.value.slice(0, -1).map((material, index) => {
        const currentPosition = flowPosition(index);
        const nextPosition = flowPosition(index + 1);
        const source = `material-${material.id ?? index}`;
        const target = `material-${learningPath.value[index + 1].id ?? index + 1}`;
        const isMovingRight = nextPosition.x > currentPosition.x;
        const isMovingLeft = nextPosition.x < currentPosition.x;
        const isThirdNode = (index + 1) % 3 === 0;
        const sourceHandle = isThirdNode
            ? 'source-bottom'
            : isMovingRight
              ? 'source-right'
              : 'source-left';
        const targetHandle = isThirdNode
            ? 'target-top'
            : isMovingRight
              ? 'target-left'
              : 'target-right';

        return {
            id: `${source}->${target}`,
            source,
            target,
            type: 'default',
            sourceHandle,
            targetHandle,
            sourcePosition: isThirdNode
                ? Position.Bottom
                : isMovingRight
                  ? Position.Right
                  : Position.Left,
            targetPosition: isThirdNode
                ? Position.Top
                : isMovingRight
                  ? Position.Left
                  : Position.Right,
            style: { stroke: '#292524', strokeWidth: 2 },
        };
    })
);

function materialIcon(type) {
    if (type === 'video') {
        return PhVideoCamera;
    }

    if (type === 'quiz') {
        return PhQuestion;
    }

    return PhBookOpen;
}
</script>

<template>
    <Head :title="`${course.title} - Kelas ${classNumber}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('courses.show', { course: course.id })"
                    class="inline-flex items-center justify-center rounded-md w-9 h-9 text-muted-foreground hover:bg-muted hover:text-foreground"
                    aria-label="Kembali ke pilihan kelas"
                >
                    <PhArrowLeft :size="18" aria-hidden="true" />
                </Link>
                <div>
                    <p class="text-sm text-muted-foreground">{{ course.title }}</p>
                    <h2 class="text-2xl font-bold text-foreground">
                        Learning path kelas {{ classNumber }}
                    </h2>
                </div>
            </div>
        </template>

        <div class="px-4 pt-6 pb-12 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <section class="p-6 border border-orange-200 rounded-xl bg-orange-50 sm:p-8">
                    <p class="text-sm font-semibold tracking-wide uppercase text-primary">
                        {{ selectedClass?.title }}
                    </p>
                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Ikuti langkah belajarmu
                    </h1>
                    <p class="max-w-2xl mt-2 text-sm leading-6 text-muted-foreground sm:text-base">
                        Selesaikan materi sesuai urutan.
                    </p>
                </section>

                <section class="mt-8" aria-labelledby="path-heading">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold tracking-wide uppercase text-primary">
                                Learning path
                            </p>
                            <h3 id="path-heading" class="mt-1 text-xl font-bold text-foreground">
                                Kelas {{ classNumber }}
                            </h3>
                        </div>
                        <span class="text-sm text-muted-foreground">
                            {{ learningPath.length }} materi
                        </span>
                    </div>

                    <div
                        v-if="learningPath.length"
                        class="relative mt-8 learning-flow"
                        :style="{ height: flowHeight }"
                    >
                        <VueFlow
                            :nodes="flowNodes"
                            :edges="flowEdges"
                            :default-viewport="{ x: 24, y: 24, zoom: 1 }"
                            :zoom-on-scroll="false"
                            :zoom-on-pinch="false"
                            :zoom-on-double-click="false"
                            :pan-on-drag="false"
                            :pan-on-scroll="false"
                            :nodes-draggable="false"
                            :nodes-connectable="false"
                            :elements-selectable="false"
                            class="learning-flow__canvas"
                        >
                            <template #node-learningMaterial="{ data }">
                                <article class="learning-node">
                                    <Handle
                                        type="target"
                                        :position="Position.Left"
                                        id="target-left"
                                        class="learning-handle"
                                    />
                                    <Handle
                                        type="source"
                                        :position="Position.Right"
                                        id="source-right"
                                        class="learning-handle"
                                    />
                                    <Handle
                                        type="target"
                                        :position="Position.Top"
                                        id="target-top"
                                        class="learning-handle"
                                    />
                                    <Handle
                                        type="source"
                                        :position="Position.Bottom"
                                        id="source-bottom"
                                        class="learning-handle"
                                    />
                                    <Handle
                                        type="source"
                                        :position="Position.Left"
                                        id="source-left"
                                        class="learning-handle"
                                    />
                                    <Handle
                                        type="target"
                                        :position="Position.Right"
                                        id="target-right"
                                        class="learning-handle"
                                    />
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="text-2xl font-semibold text-foreground">
                                            {{ data.index + 1 }}
                                        </span>
                                        <component
                                            :is="materialIcon(data.material.type)"
                                            :size="18"
                                            class="mt-1 text-primary"
                                            aria-hidden="true"
                                        />
                                    </div>
                                    <h4
                                        class="mt-4 text-sm font-semibold leading-5 text-foreground"
                                    >
                                        {{ data.material.title }}
                                    </h4>
                                </article>
                            </template>
                        </VueFlow>
                    </div>

                    <div
                        v-else
                        class="p-8 mt-6 text-center border border-dashed rounded-xl border-border"
                    >
                        <p class="font-semibold text-foreground">Belum ada materi</p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Materi untuk kelas ini sedang disiapkan.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.learning-flow {
    overflow: hidden;
}

.learning-flow__canvas {
    min-width: 52rem;
}

.learning-node {
    width: 13rem;
    min-height: 7rem;
    padding: 1.25rem;
    border: 2px solid theme('colors.stone.800');
    border-radius: 1rem;
    background: white;
    box-shadow: 0 4px 0 theme('colors.stone.800');
}

.learning-handle {
    opacity: 0;
}
</style>
