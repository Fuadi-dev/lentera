<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CourseMaterialSidebar from '@/Components/Course/CourseMaterialSidebar.vue';
import QuestionMaterial from '@/Components/Course/QuestionMaterial.vue';
import QuizActivity from '@/Components/Course/QuizActivity.vue';
import TextMaterial from '@/Components/Course/TextMaterial.vue';
import VideoMaterial from '@/Components/Course/VideoMaterial.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    PhArrowLeft,
    PhCaretDoubleLeft,
    PhCaretDoubleRight,
    PhCheckCircle,
    PhList,
} from '@phosphor-icons/vue';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/Components/ui/sheet';

const props = defineProps({
    course: { type: Object, required: true },
    classNumber: { type: [Number, String], required: true },
    material: { type: Object, required: true },
    isCompleted: { type: Boolean, default: false },
    completedUnitIds: { type: Array, default: () => [] },
});

const desktopSidebarOpen = ref(true);
const mobileSidebarOpen = ref(false);
const quizCompleted = ref(null);
const DESKTOP_SIDEBAR_STORAGE_KEY = 'lentera:course-material-sidebar-open';

onMounted(() => {
    const savedSidebarState = localStorage.getItem(DESKTOP_SIDEBAR_STORAGE_KEY);

    if (savedSidebarState !== null) {
        desktopSidebarOpen.value = savedSidebarState === 'true';
    }
});

watch(desktopSidebarOpen, (isOpen) => {
    localStorage.setItem(DESKTOP_SIDEBAR_STORAGE_KEY, String(isOpen));
});

const currentLevelUnits = computed(() => {
    const selectedClass = (props.course.classes ?? []).find(
        (courseClass) => Number(courseClass.number) === Number(props.classNumber)
    );

    for (const level of selectedClass?.levels ?? []) {
        const levelChapters = level.chapters ?? [];

        if (
            levelChapters.some((chapter) =>
                (chapter.units ?? []).some((unit) => unit.id === props.material.id)
            )
        ) {
            return levelChapters.flatMap((chapter) => chapter.units ?? []);
        }
    }

    return [];
});

const isCurrentLevelCompleted = computed(
    () =>
        currentLevelUnits.value.length > 0 &&
        currentLevelUnits.value.every((unit) => props.completedUnitIds.includes(unit.id))
);

const chapterUnits = computed(() => {
    const selectedClass = (props.course.classes ?? []).find(
        (courseClass) => courseClass.number === Number(props.classNumber)
    );

    return (
        selectedClass?.levels
            ?.flatMap((level) => level.chapters ?? [])
            .find((chapter) => chapter.units?.some((unit) => unit.id === props.material.id))
            ?.units ?? []
    );
});

const navigationUnits = computed(() =>
    (props.course.classes ?? []).flatMap((courseClass) =>
        (courseClass.levels ?? []).flatMap((level) =>
            (level.chapters ?? []).flatMap((chapter) =>
                (chapter.units ?? []).map((unit) => ({
                    classNumber: courseClass.number,
                    chapterId: chapter.id,
                    unit,
                }))
            )
        )
    )
);

const nextUnit = computed(() => {
    const currentIndex = navigationUnits.value.findIndex(
        ({ unit }) => unit.id === props.material.id
    );

    return currentIndex >= 0 ? navigationUnits.value[currentIndex + 1] : null;
});

const nextUnitHref = computed(() =>
    nextUnit.value
        ? route('courses.material', {
              course: props.course.id,
              class: nextUnit.value.classNumber,
              material: nextUnit.value.unit.id,
          })
        : null
);

function handleQuizCompleted(result) {
    quizCompleted.value = result;
}
</script>

<template>
    <Head :title="`${material.title} - ${course.title}`" />

    <AuthenticatedLayout>
        <Sheet v-model:open="mobileSidebarOpen">
            <SheetTrigger as-child>
                <button
                    type="button"
                    class="fixed left-0 z-30 flex size-12 items-center justify-center rounded-r-full bg-orange-600 text-white shadow-lg transition hover:bg-orange-700 focus:outline-none focus:ring-4 focus:ring-orange-200 cursor-pointer top-36 sm:top-20 md:hidden"
                    aria-label="Buka daftar materi"
                    title="Daftar Materi Pembelajaran"
                >
                    <PhList :size="24" weight="bold" aria-hidden="true" />
                </button>
            </SheetTrigger>

            <SheetContent
                side="left"
                class="w-[85vw] max-w-sm p-0 bg-white border-0 outline-none ring-0 shadow-2xl flex flex-col h-full"
            >
                <SheetHeader class="sr-only">
                    <SheetTitle>Daftar Materi Pembelajaran</SheetTitle>
                    <SheetDescription>Daftar bab dan unit materi pelajaran</SheetDescription>
                </SheetHeader>
                <CourseMaterialSidebar
                    :course="course"
                    :class-number="classNumber"
                    :material="material"
                    :completed-unit-ids="completedUnitIds"
                    @select-unit="mobileSidebarOpen = false"
                />
            </SheetContent>
        </Sheet>

        <div class="flex min-h-[calc(100vh-4rem)] items-start">
            <aside
                class="relative hidden sticky top-16 h-[calc(100vh-4rem)] shrink-0 overflow-hidden border-r border-slate-200 bg-white transition-[width] duration-300 ease-in-out md:block"
                :class="desktopSidebarOpen ? 'w-96' : 'w-0 border-r-0'"
                aria-label="Daftar materi pembelajaran"
            >
                <button
                    v-if="desktopSidebarOpen"
                    type="button"
                    class="absolute right-3 top-4 z-10 flex size-8 items-center justify-center rounded-lg text-stone-500 transition hover:bg-stone-100 hover:text-stone-900 focus:outline-none focus:ring-2 focus:ring-orange-500 cursor-pointer"
                    aria-label="Tutup daftar materi"
                    title="Tutup daftar materi"
                    @click="desktopSidebarOpen = false"
                >
                    <PhCaretDoubleLeft :size="20" weight="bold" aria-hidden="true" />
                </button>
                <div class="h-full w-96">
                    <CourseMaterialSidebar
                        :course="course"
                        :class-number="classNumber"
                        :material="material"
                        :completed-unit-ids="completedUnitIds"
                    />
                </div>
            </aside>

            <button
                v-if="!desktopSidebarOpen"
                type="button"
                class="fixed left-0 top-36 z-30 hidden size-12 items-center justify-center rounded-r-full bg-orange-600 text-white shadow-lg transition hover:bg-orange-700 focus:outline-none focus:ring-4 focus:ring-orange-200 cursor-pointer md:flex sm:top-20"
                aria-label="Buka daftar materi"
                title="Daftar Materi Pembelajaran"
                @click="desktopSidebarOpen = true"
            >
                <PhCaretDoubleRight :size="24" weight="bold" aria-hidden="true" />
            </button>

            <div class="min-w-0 flex-1 px-4 pt-6 pb-16 sm:px-6 lg:px-8">
                <div class="max-w-4xl mx-auto space-y-6">
                    <div
                        v-if="isCurrentLevelCompleted"
                        class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800 shadow-sm"
                        role="status"
                    >
                        <PhCheckCircle
                            :size="18"
                            weight="fill"
                            class="shrink-0 text-emerald-600"
                            aria-hidden="true"
                        />
                        <span class="font-medium"
                            >Semua materi selesai! Level selanjutnya terbuka.</span
                        >
                    </div>

                    <!-- Header Breadcrumb & Back -->
                    <header class="flex items-center gap-3 pb-4 border-b border-border/60">
                        <Link
                            :href="
                                route('courses.learning-path', {
                                    course: course.id,
                                    class: classNumber,
                                })
                            "
                            class="inline-flex items-center justify-center rounded-xl size-10 text-muted-foreground hover:bg-muted hover:text-foreground transition-colors shrink-0"
                            aria-label="Kembali ke jalur belajar"
                            title="Kembali ke jalur belajar"
                        >
                            <PhArrowLeft :size="20" weight="bold" aria-hidden="true" />
                        </Link>
                        <div class="min-w-0">
                            <p
                                class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                            >
                                {{ course.title }}
                            </p>
                            <h1 class="text-lg sm:text-xl font-bold truncate text-foreground">
                                {{ material.title }}
                            </h1>
                        </div>
                    </header>

                    <!-- Main Material Card Container -->
                    <main class="w-full">
                        <TextMaterial
                            v-if="material.type === 'text'"
                            :course="course"
                            :class-number="classNumber"
                            :material="material"
                            :next-unit-href="nextUnitHref"
                            :next-unit-title="nextUnit?.unit.title"
                            :completed="isCompleted"
                        />
                        <VideoMaterial
                            v-else-if="material.type === 'video'"
                            :material="material"
                            :next-unit-href="nextUnitHref"
                            :next-unit-title="nextUnit?.unit.title"
                            :completed="isCompleted"
                        />
                        <QuestionMaterial
                            v-else-if="material.type === 'question'"
                            :material="material"
                            :next-unit-href="nextUnitHref"
                            :next-unit-title="nextUnit?.unit.title"
                            :completed="isCompleted"
                        />
                        <template v-else-if="material.type === 'quiz'">
                            <QuizActivity
                                :unit="material"
                                :chapter-units="chapterUnits"
                                :next-unit-href="nextUnitHref"
                                :next-unit-title="nextUnit?.unit.title"
                                :completed="isCompleted"
                                @completed="handleQuizCompleted"
                            />
                            <p
                                v-if="quizCompleted"
                                class="mt-4 text-sm font-semibold text-green-600"
                                aria-live="polite"
                            >
                                Quiz selesai.
                            </p>
                        </template>
                    </main>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
