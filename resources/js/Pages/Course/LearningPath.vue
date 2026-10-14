<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    PhArrowLeft,
    PhBookOpen,
    PhInfo,
    PhLock,
    PhQuestion,
    PhVideoCamera,
} from '@phosphor-icons/vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/Components/ui/alert-dialog';
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

const NODE_WIDTH = 240;
const NODE_MIN_HEIGHT = 112;
const MIN_GAP_X = 48;
const STEP_Y = 140;
const ROW_GAP = 56;
const PADDING_Y = 24;

const selectedClass = computed(() =>
    props.course.classes?.find((courseClass) => courseClass.number === Number(props.classNumber))
);

const learningPath = computed(() => {
    const levels = selectedClass.value?.levels ?? [];

    return levels.map((level) => {
        const firstChapter = level.chapters?.[0];
        const firstUnit = firstChapter?.units?.[0];

        return {
            id: level.id,
            title: level.title,
            description: level.description,
            type: 'level',
            number: level.number,
            firstUnitId: firstUnit?.id,
            isLocked: level.isLocked,
        };
    });
});

const flowContainer = ref(null);
const containerWidth = ref(0);
let resizeObserver = null;

watch(
    flowContainer,
    (element) => {
        resizeObserver?.disconnect();

        if (!element) {
            return;
        }

        containerWidth.value = element.clientWidth;
        resizeObserver = new ResizeObserver(([entry]) => {
            containerWidth.value = entry.contentRect.width;
        });
        resizeObserver.observe(element);
    },
    { flush: 'post' }
);

onBeforeUnmount(() => resizeObserver?.disconnect());

const columns = computed(() => {
    const maxColumns = Math.max(
        1,
        Math.floor((containerWidth.value + MIN_GAP_X) / (NODE_WIDTH + MIN_GAP_X))
    );

    return Math.min(maxColumns, Math.max(1, learningPath.value.length));
});

const gapX = computed(() => {
    if (columns.value < 2) {
        return 0;
    }

    return (containerWidth.value - columns.value * NODE_WIDTH) / (columns.value - 1);
});

const offsetX = computed(() =>
    columns.value < 2 ? Math.max(0, (containerWidth.value - NODE_WIDTH) / 2) : 0
);

const flowHeight = computed(() => {
    const total = learningPath.value.length;

    if (!total) {
        return '0px';
    }

    const rows = Math.ceil(total / columns.value);
    const lastY = (total - 1) * STEP_Y + (rows - 1) * ROW_GAP;

    return `${lastY + NODE_MIN_HEIGHT + PADDING_Y * 2}px`;
});

function flowPosition(index) {
    const cols = columns.value;
    const row = Math.floor(index / cols);
    const col = index % cols;
    const isMovingRight = row % 2 === 0;
    const stepX = NODE_WIDTH + gapX.value;

    return {
        x: offsetX.value + (isMovingRight ? col : cols - 1 - col) * stepX,
        y: index * STEP_Y + row * ROW_GAP,
    };
}

const flowNodes = computed(() =>
    learningPath.value.map((material, index) => ({
        id: `material-${material.id ?? index}`,
        type: 'learningMaterial',
        position: flowPosition(index),
        data: { material, index },
    }))
);

const flowEdges = computed(() =>
    learningPath.value.slice(0, -1).map((material, index) => {
        const cols = columns.value;
        const source = `material-${material.id ?? index}`;
        const target = `material-${learningPath.value[index + 1].id ?? index + 1}`;
        const goesDown = (index + 1) % cols === 0;
        const isMovingRight = Math.floor(index / cols) % 2 === 0;

        const sourceHandle = goesDown
            ? 'source-bottom'
            : isMovingRight
              ? 'source-right'
              : 'source-left';
        const targetHandle = goesDown
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
            sourcePosition: goesDown
                ? Position.Bottom
                : isMovingRight
                  ? Position.Right
                  : Position.Left,
            targetPosition: goesDown
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

const isLockedModalOpen = ref(false);
const lockedModalLevel = ref(null);

function handleLockedNodeClick(material) {
    if (material.isLocked) {
        lockedModalLevel.value = material;
        isLockedModalOpen.value = true;
    }
}

function openMaterial({ event, node }) {
    if (event.target instanceof Element && event.target.closest('a')) {
        return;
    }

    if (node.data.material.isLocked) {
        handleLockedNodeClick(node.data.material);
        return;
    }

    if (!node.data.material.firstUnitId) {
        return;
    }

    router.visit(
        route('courses.material', {
            course: props.course.id,
            class: props.classNumber,
            material: node.data.material.firstUnitId,
        })
    );
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
                        Jalur Belajar kelas {{ classNumber }}
                    </h2>
                </div>
            </div>
        </template>

        <div class="px-4 pt-6 pb-12 sm:px-6 lg:px-8">
            <div class="max-w-[76rem] mx-auto">
                <section
                    class="relative flex items-center justify-between overflow-hidden border-2 bg-gray-50 sm:rounded-lg"
                >
                    <span
                        class="absolute text-3xl select-none right-32 top-5 md:right-56 twinkle"
                        aria-hidden="true"
                        >⭐</span
                    >
                    <span
                        class="absolute text-2xl select-none right-24 bottom-6 md:right-44 twinkle"
                        style="animation-delay: 0.8s"
                        aria-hidden="true"
                        >✨</span
                    >

                    <div class="p-6 md:p-10">
                        <p class="text-sm font-semibold tracking-wide uppercase text-primary">
                            {{ selectedClass?.title }}
                        </p>
                        <h1
                            class="mt-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                        >
                            Ikuti langkah belajarmu
                        </h1>
                        <p
                            class="max-w-2xl mt-2 text-sm leading-6 text-muted-foreground sm:text-base"
                        >
                            Selesaikan materi sesuai urutan.
                        </p>
                    </div>
                    <img
                        src="/images/brand/maskot.png"
                        alt="Maskot Lentera"
                        class="w-24 h-full py-4 mr-6 md:w-36 md:mr-12"
                    />
                </section>

                <!-- Flash Alert jika dialihkan dari materi terkunci -->
                <div
                    v-if="$page.props.flash?.warning"
                    class="flex items-start gap-3 p-4 mt-6 border rounded-2xl bg-amber-50 border-amber-300 text-amber-900 shadow-sm"
                    role="alert"
                >
                    <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-amber-200/70 text-amber-800 shrink-0 mt-0.5">
                        <PhLock :size="18" weight="bold" />
                    </div>
                    <div>
                        <p class="font-bold text-amber-950">Akses Terkunci</p>
                        <p class="mt-0.5 text-sm text-amber-800">{{ $page.props.flash.warning }}</p>
                    </div>
                </div>

                <!-- Info Alert tentang Mekanisme Buka Level -->
                <div
                    class="flex items-start gap-3.5 p-4 sm:p-5 mt-6 border-2 rounded-2xl bg-amber-50/70 border-amber-200 text-amber-950 shadow-sm"
                    role="region"
                    aria-label="Informasi Jalur Belajar"
                >
                    <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-amber-100 text-amber-700 shrink-0 mt-0.5">
                        <PhInfo :size="22" weight="bold" />
                    </div>
                    <div class="text-sm">
                        <p class="font-bold text-amber-950 text-base">Petunjuk Pembukaan Level</p>
                        <p class="mt-1 text-amber-800 leading-relaxed">
                            Level berikutnya akan terbuka secara otomatis setelah kamu menandai selesai seluruh materi/unit pada level sebelumnya. Klik pada level yang terkunci untuk melihat detailnya.
                        </p>
                    </div>
                </div>

                <section class="mt-8" aria-labelledby="path-heading">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold tracking-wide uppercase text-primary">
                                Jalur Belajar
                            </p>
                            <h3 id="path-heading" class="mt-1 text-xl font-bold text-foreground">
                                Kelas {{ classNumber }}
                            </h3>
                        </div>
                        <span class="text-sm text-muted-foreground">
                            {{ learningPath.length }} level
                        </span>
                    </div>

                    <div
                        v-if="learningPath.length"
                        ref="flowContainer"
                        class="relative mt-8 learning-flow"
                        :style="{ height: flowHeight }"
                    >
                        <VueFlow
                            v-if="containerWidth > 0"
                            :nodes="flowNodes"
                            :edges="flowEdges"
                            :default-viewport="{ x: 0, y: PADDING_Y, zoom: 1 }"
                            :zoom-on-scroll="false"
                            :zoom-on-pinch="false"
                            :zoom-on-double-click="false"
                            :pan-on-drag="false"
                            :pan-on-scroll="false"
                            :nodes-draggable="false"
                            :nodes-connectable="false"
                            :elements-selectable="false"
                            @node-click="openMaterial"
                            class="learning-flow__canvas"
                        >
                            <template #node-learningMaterial="{ data }">
                                <component
                                    :is="
                                        data.material.firstUnitId && !data.material.isLocked
                                            ? Link
                                            : 'div'
                                    "
                                    :href="
                                        data.material.firstUnitId && !data.material.isLocked
                                            ? route('courses.material', {
                                                  course: course.id,
                                                  class: classNumber,
                                                  material: data.material.firstUnitId,
                                              })
                                            : undefined
                                    "
                                    :class="[
                                        'block learning-node',
                                        data.material.firstUnitId && !data.material.isLocked
                                            ? 'cursor-pointer transition-transform hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2'
                                            : 'learning-node--locked cursor-pointer transition-transform hover:scale-[1.02]',
                                    ]"
                                    :aria-label="
                                        data.material.firstUnitId && !data.material.isLocked
                                            ? `Buka level ${data.material.title}`
                                            : `${data.material.title} terkunci. Klik untuk informasi.`
                                    "
                                    :aria-disabled="
                                        data.material.isLocked || !data.material.firstUnitId
                                            ? 'true'
                                            : undefined
                                    "
                                    @click="handleLockedNodeClick(data.material)"
                                >
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
                                        <PhLock
                                            v-if="data.material.isLocked"
                                            :size="18"
                                            class="mt-1 text-amber-600"
                                            aria-label="Terkunci"
                                        />
                                        <component
                                            v-else
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
                                </component>
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

        <!-- Modal Alert ketika Siswa Menekan Level Terkunci -->
        <AlertDialog :open="isLockedModalOpen" @update:open="isLockedModalOpen = $event">
            <AlertDialogContent class="border-2 rounded-2xl border-stone-800 shadow-2xl max-w-md">
                <AlertDialogHeader class="flex flex-col items-center text-center">
                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 mb-2">
                        <PhLock :size="28" weight="bold" />
                    </div>
                    <AlertDialogTitle class="text-xl font-bold text-foreground">
                        Level Masih Terkunci
                    </AlertDialogTitle>
                    <AlertDialogDescription class="text-sm text-muted-foreground mt-2 leading-relaxed">
                        Level <span class="font-semibold text-foreground">"{{ lockedModalLevel?.title }}"</span> belum dapat diakses. Selesaikan dan tandai selesai seluruh materi/unit di level sebelumnya terlebih dahulu untuk membuka level ini.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter class="sm:justify-center mt-6">
                    <AlertDialogAction
                        @click="isLockedModalOpen = false"
                        class="w-full sm:w-auto font-semibold px-6"
                    >
                        Saya Mengerti
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AuthenticatedLayout>
</template>

<style scoped>
.learning-flow {
    overflow: hidden;
}

.learning-flow__canvas {
    width: 100%;
}

.learning-node {
    width: 15rem;
    min-height: 7rem;
    padding: 1.25rem;
    border: 2px solid theme('colors.stone.800');
    border-radius: 1rem;
    background: white;
    box-shadow: 0 4px 0 theme('colors.stone.800');
    pointer-events: auto;
}

.learning-node--locked {
    border-color: theme('colors.stone.300');
    background: theme('colors.stone.100');
    box-shadow: 0 4px 0 theme('colors.stone.300');
    cursor: pointer;
    opacity: 0.85;
}

.learning-handle {
    width: 0;
    height: 0;
    min-width: 0;
    min-height: 0;
    border: 0;
    opacity: 0;
    pointer-events: none;
}

.learning-handle.vue-flow__handle-left {
    left: 0;
}

.learning-handle.vue-flow__handle-right {
    right: 0;
}

.learning-handle.vue-flow__handle-top {
    top: 0;
}

.learning-handle.vue-flow__handle-bottom {
    bottom: 0;
}
</style>
