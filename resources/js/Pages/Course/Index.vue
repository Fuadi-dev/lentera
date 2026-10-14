<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { PhArrowLeft, PhArrowRight, PhInfo, PhLock, PhSparkle } from '@phosphor-icons/vue';

const props = defineProps({
    course: {
        type: Object,
        required: true,
    },
});

function unlockedLevelsCount(courseClass) {
    if (!courseClass.levels?.length) return 0;
    return courseClass.levels.filter((level) => !level.isLocked).length;
}
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

                <!-- Flash Alert jika ada redirect dari level terkunci -->
                <div
                    v-if="$page.props.flash?.warning"
                    class="flex items-start gap-3 p-4 border rounded-2xl bg-amber-50 border-amber-300 text-amber-900 shadow-sm"
                    role="alert"
                >
                    <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-amber-200/70 text-amber-800 shrink-0 mt-0.5">
                        <PhLock :size="18" weight="bold" />
                    </div>
                    <div>
                        <p class="font-bold text-amber-950">Akses Dibatasi</p>
                        <p class="mt-0.5 text-sm text-amber-800">{{ $page.props.flash.warning }}</p>
                    </div>
                </div>

                <!-- Info Alert tentang Mekanisme Level Unlock -->
                <div
                    class="flex items-start gap-3.5 p-4 sm:p-5 border-2 rounded-2xl bg-amber-50/70 border-amber-200 text-amber-950 shadow-sm"
                    role="region"
                    aria-label="Informasi Jalur Belajar"
                >
                    <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-amber-100 text-amber-700 shrink-0 mt-0.5">
                        <PhInfo :size="22" weight="bold" />
                    </div>
                    <div class="text-sm">
                        <p class="font-bold text-amber-950 text-base">Petunjuk Pembukaan Level</p>
                        <p class="mt-1 text-amber-800 leading-relaxed">
                            Level berikutnya akan terbuka secara otomatis setelah kamu menandai selesai seluruh unit materi pada level sebelumnya. Selesaikan tantangannya langkah demi langkah!
                        </p>
                    </div>
                </div>

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
                            class="flex flex-col p-5 text-left transition-all duration-200 border group min-h-56 rounded-2xl border-border bg-card hover:-translate-y-1 hover:shadow-md"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-base font-semibold text-muted-foreground">
                                    Kelas {{ courseClass.number }}
                                </span>
                                <span
                                    v-if="courseClass.levels?.length"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full bg-primary/10 text-primary"
                                >
                                    <PhSparkle :size="13" weight="fill" />
                                    {{ unlockedLevelsCount(courseClass) }} / {{ courseClass.levels.length }} Level Terbuka
                                </span>
                            </div>

                            <span class="block mt-4 text-2xl font-bold text-foreground">
                                {{ courseClass.title }}
                            </span>
                            <span class="block mt-2 text-sm leading-6 text-muted-foreground">
                                {{ courseClass.description }}
                            </span>

                            <span
                                class="flex items-center justify-between gap-3 pt-5 mt-auto text-sm font-medium text-primary"
                            >
                                <span>Mulai belajar</span>
                                <PhArrowRight
                                    :size="18"
                                    class="transition-transform duration-200 group-hover:translate-x-1"
                                    aria-hidden="true"
                                />
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
