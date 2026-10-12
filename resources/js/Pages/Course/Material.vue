<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CourseMaterialSidebar from '@/Components/Course/CourseMaterialSidebar.vue';
import QuestionMaterial from '@/Components/Course/QuestionMaterial.vue';
import QuizActivity from '@/Components/Course/QuizActivity.vue';
import TextMaterial from '@/Components/Course/TextMaterial.vue';
import VideoMaterial from '@/Components/Course/VideoMaterial.vue';
import NextMaterialButton from '@/Components/Course/NextMaterialButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { PhArrowLeft } from '@phosphor-icons/vue';
import { SidebarProvider } from '@/Components/ui/sidebar';

const props = defineProps({
    course: { type: Object, required: true },
    classNumber: { type: [Number, String], required: true },
    material: { type: Object, required: true },
    isCompleted: { type: Boolean, default: false },
    completedUnitIds: { type: Array, default: () => [] },
});

const sidebarOpen = ref(true);
const quizCompleted = ref(null);

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

console.log("kuis komplete su", props.isCompleted)
</script>

<template>
    <Head :title="`${material.title} - ${course.title}`" />

    <AuthenticatedLayout>
        <div class="px-4 pt-6 pb-12 sm:px-6 lg:px-8">
            <div class="max-w-[76rem] mx-auto space-y-6">
                <header
                    class="flex items-center gap-3 px-4 pt-6 pb-12 border-b border-border h-14 shrink-0"
                >
                    <Link
                        :href="
                            route('courses.learning-path', {
                                course: course.id,
                                class: classNumber,
                            })
                        "
                        class="inline-flex items-center justify-center rounded-md w-9 h-9 text-muted-foreground hover:bg-muted hover:text-foreground"
                        aria-label="Kembali ke jalur belajar"
                    >
                        <PhArrowLeft :size="18" aria-hidden="true" />
                    </Link>
                    <div class="min-w-0">
                        <p class="text-sm truncate text-muted-foreground">{{ course.title }}</p>
                        <h2 class="font-bold truncate text-foreground">{{ material.title }}</h2>
                    </div>
                </header>

                <div class="relative">
                    <SidebarProvider v-model:open="sidebarOpen" class="w-full min-h-0">
                        <CourseMaterialSidebar
                            :course="course"
                            :class-number="classNumber"
                            :material="material"
                            :completed-unit-ids="completedUnitIds"
                        />

                        <main class="flex-1 w-full min-w-0">
                            <div class="px-4">
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
                            </div>
                        </main>
                    </SidebarProvider>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
