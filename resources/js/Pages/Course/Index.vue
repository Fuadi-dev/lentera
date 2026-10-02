<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { PhArrowLeft, PhArrowRight, PhBookOpen } from '@phosphor-icons/vue';

const props = defineProps({
    course: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="course.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center justify-center rounded-md w-9 h-9 text-muted-foreground hover:bg-muted hover:text-foreground"
                    aria-label="Kembali ke dashboard"
                >
                    <PhArrowLeft :size="18" aria-hidden="true" />
                </Link>
                <h2 class="text-2xl font-bold text-foreground">{{ course.title }}</h2>
            </div>
        </template>

        <div class="px-4 pt-6 pb-12 sm:px-6 lg:px-8">
            <div class="mx-auto space-y-8 max-w-[76rem]">
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
                            Course
                        </p>
                        <h1
                            class="mt-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                        >
                            {{ course.title }}
                        </h1>
                        <p class="max-w-3xl mt-2 text-sm text-muted-foreground sm:text-base">
                            {{ course.description }}
                        </p>
                    </div>
                    <img
                        src="/images/brand/maskot.png"
                        alt="Maskot Lentera"
                        class="w-24 h-full py-4 mr-6 md:w-36 md:mr-12"
                    />
                </section>

                <section class="space-y-4" aria-labelledby="class-selection-heading">
                    <div>
                        <h3
                            id="class-selection-heading"
                            class="mt-1 text-xl font-bold text-foreground"
                        >
                            Pilih kelas
                        </h3>
                    </div>

                    <div
                        v-if="course.classes?.length"
                        class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <Link
                            v-for="courseClass in course.classes"
                            :key="courseClass.id"
                            :href="
                                route('courses.learning-path', {
                                    course: course.id,
                                    class: courseClass.number,
                                })
                            "
                            type="button"
                            class="p-5 text-left transition-colors border rounded-xl border-border bg-card hover:border-primary"
                        >
                            <span class="flex items-center justify-between gap-3">
                                <p class="font-bold text-md">Kelas</p>
                                <span
                                    class="inline-flex items-center justify-center w-10 h-10 text-sm font-bold rounded-full bg-primary text-primary-foreground"
                                >
                                    {{ courseClass.number }}
                                </span>
                            </span>
                            <span class="block mt-4 text-lg font-bold text-foreground">
                                {{ courseClass.title }}
                            </span>
                            <span class="block mt-2 text-sm leading-6 text-muted-foreground">
                                {{ courseClass.description }}
                            </span>
                            <span
                                class="flex items-center gap-2 mt-4 text-sm font-medium text-primary"
                            >
                                <PhBookOpen :size="17" aria-hidden="true" />
                                {{ courseClass.section?.length ?? 0 }} bab
                                <PhArrowRight :size="16" aria-hidden="true" />
                            </span>
                        </Link>
                    </div>

                    <div
                        v-else
                        class="p-8 text-center border border-dashed rounded-xl border-border"
                    >
                        <p class="font-semibold text-foreground">Belum ada kelas</p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Materi untuk course ini sedang disiapkan.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
