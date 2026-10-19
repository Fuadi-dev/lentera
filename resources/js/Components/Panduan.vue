<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import {
    BookOpen,
    CheckCircle2,
    Flame,
    GraduationCap,
    KeyRound,
    Link2,
    UserPlus,
    UsersRound,
} from 'lucide-vue-next';
import '@vue-flow/core/dist/style.css';
import '@vue-flow/core/dist/theme-default.css';
import { Handle, Position, VueFlow } from '@vue-flow/core';

const steps = [
    {
        number: '01',
        title: 'Buat Akun Anak',
        description: 'Daftar di Lentera sebagai anak dengan nama, email, dan kata sandi.',
        icon: UserPlus,
    },
    {
        number: '02',
        title: 'Buat Akun Orang Tua',
        description: 'Orang tua juga mendaftar sebagai orang tua agar dapat memantau belajar anak.',
        icon: UsersRound,
    },
    {
        number: '03',
        title: 'Ambil Kode Hubung',
        description: 'Anak membuka Profil dan menyalin Kode Hubung Orang Tua miliknya.',
        icon: KeyRound,
    },
    {
        number: '04',
        title: 'Masukkan Kode Anak',
        description:
            'Orang tua membuka dashboard, memilih Hubungkan Anak, lalu memasukkan kode tersebut.',
        icon: Link2,
    },
    {
        number: '05',
        title: 'Akun Saling Terhubung',
        description:
            'Setelah berhasil terhubung, orang tua dapat memantau aktivitas dan perkembangan belajar anak.',
        icon: CheckCircle2,
    },
    {
        number: '06',
        title: 'Pilih Mata Pelajaran',
        description: 'Anak memilih mata pelajaran dan menentukan tingkat kelas yang sesuai.',
        icon: GraduationCap,
    },
    {
        number: '07',
        title: 'Selesaikan Materi',
        description:
            'Ikuti urutan level dan tuntaskan setiap materi agar pembelajaran terus terbuka.',
        icon: BookOpen,
    },
    {
        number: '08',
        title: 'Pertahankan Api Belajar',
        description: 'Kerjakan minimal satu materi setiap hari agar streak belajar tetap menyala.',
        icon: Flame,
    },
];

const NODE_WIDTH = 250;
const NODE_HEIGHT = 280;
const MIN_GAP_X = 32;
const STEP_Y = 260;
const ROW_GAP = 80;
const PADDING_Y = 20;

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

    return Math.min(maxColumns, 4, steps.length);
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
    const rows = Math.ceil(steps.length / columns.value);

    return `${(rows - 1) * (STEP_Y + ROW_GAP) + NODE_HEIGHT + PADDING_Y * 2}px`;
});

function flowPosition(index) {
    const row = Math.floor(index / columns.value);
    const column = index % columns.value;
    const isMovingRight = row % 2 === 0;
    const stepX = NODE_WIDTH + gapX.value;

    return {
        x: offsetX.value + (isMovingRight ? column : columns.value - 1 - column) * stepX,
        y: row * (STEP_Y + ROW_GAP),
    };
}

const flowNodes = computed(() =>
    steps.map((step, index) => ({
        id: `guide-${step.number}`,
        type: 'guide',
        position: flowPosition(index),
        data: { step, index },
    }))
);

const flowEdges = computed(() =>
    steps.slice(0, -1).map((step, index) => {
        const goesDown = (index + 1) % columns.value === 0;
        const isMovingRight = Math.floor(index / columns.value) % 2 === 0;
        const source = `guide-${step.number}`;
        const target = `guide-${steps[index + 1].number}`;

        return {
            id: `${source}->${target}`,
            source,
            target,
            type: 'bezier',
            sourceHandle: goesDown
                ? 'source-bottom'
                : isMovingRight
                  ? 'source-right'
                  : 'source-left',
            targetHandle: goesDown ? 'target-top' : isMovingRight ? 'target-left' : 'target-right',
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
            style: { stroke: '#ea580c', strokeWidth: 2 },
        };
    })
);
</script>

<template>
    <section id="panduan" class="py-20 bg-background sm:py-24">
        <div class="w-full max-w-6xl px-4 mx-auto sm:px-6">
            <div class="max-w-2xl panduan-header">
                <p class="text-sm font-semibold tracking-wide uppercase text-primary">Panduan</p>
                <h2
                    class="mt-3 text-3xl font-bold leading-tight tracking-tight text-foreground sm:text-4xl"
                >
                    Cara Menggunakan Lentera
                </h2>
                <p class="mt-3 text-base text-muted-foreground">
                    Mulai dari membuat akun, menghubungkan anak dan orang tua, hingga belajar
                    secara konsisten.
                </p>
            </div>

            <div
                ref="flowContainer"
                class="relative mt-12 panduan-flow"
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
                    class="panduan-flow__canvas"
                >
                    <template #node-guide="{ data }">
                        <div class="panduan-card">
                            <Handle
                                type="target"
                                :position="Position.Left"
                                id="target-left"
                                class="panduan-handle"
                            />
                            <Handle
                                type="source"
                                :position="Position.Right"
                                id="source-right"
                                class="panduan-handle"
                            />
                            <Handle
                                type="target"
                                :position="Position.Top"
                                id="target-top"
                                class="panduan-handle"
                            />
                            <Handle
                                type="source"
                                :position="Position.Bottom"
                                id="source-bottom"
                                class="panduan-handle"
                            />
                            <Handle
                                type="source"
                                :position="Position.Left"
                                id="source-left"
                                class="panduan-handle"
                            />
                            <Handle
                                type="target"
                                :position="Position.Right"
                                id="target-right"
                                class="panduan-handle"
                            />
                            <div class="flex flex-col h-full">
                                <div class="flex items-center justify-between mb-5">
                                    <div
                                        class="flex items-center justify-center rounded-xl size-11 bg-primary/10 text-primary"
                                    >
                                        <component :is="data.step.icon" class="size-5" />
                                    </div>
                                    <span class="text-sm font-bold text-muted-foreground">
                                        {{ data.step.number }}
                                    </span>
                                </div>

                                <h3 class="text-lg font-bold leading-snug text-foreground">
                                    {{ data.step.title }}
                                </h3>
                                <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                                    {{ data.step.description }}
                                </p>
                            </div>
                        </div>
                    </template>
                </VueFlow>
            </div>
        </div>
    </section>
</template>

<style scoped>
.panduan-flow {
    overflow: hidden;
}

.panduan-flow__canvas {
    width: 100%;
}

.panduan-card {
    width: 15.625rem;
    height: 17.5rem;
    min-height: 17.5rem;
    padding: 1.5rem;
    border: 1px solid theme('colors.border');
    border-radius: 1rem;
    background: white;
    box-sizing: border-box;
    box-shadow: 0 4px 12px rgb(41 37 36 / 0.06);
    cursor: default;
    pointer-events: auto;
    transition: box-shadow 200ms ease;
}

.panduan-card:hover {
    box-shadow: 0 8px 24px rgb(234 88 12 / 0.22);
}

.panduan-flow__canvas :deep(.vue-flow__node) {
    cursor: default;
}

.panduan-handle {
    width: 0;
    height: 0;
    min-width: 0;
    min-height: 0;
    border: 0;
    opacity: 0;
    pointer-events: none;
}

.panduan-handle.vue-flow__handle-left {
    left: 0;
}

.panduan-handle.vue-flow__handle-right {
    right: 0;
}

.panduan-handle.vue-flow__handle-top {
    top: 0;
}

.panduan-handle.vue-flow__handle-bottom {
    bottom: 0;
}
</style>
