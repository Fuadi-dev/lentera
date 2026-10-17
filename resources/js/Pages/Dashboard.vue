<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { PhArrowRight, PhMagnifyingGlass } from '@phosphor-icons/vue';
import Button from '@/Components/ui/button/Button.vue';
import Card from '@/Components/ui/card/Card.vue';
import Input from '@/Components/ui/input/Input.vue';
import coursesData from '../../../data/courses.json';

const page = usePage();

const props = defineProps({
    courses: {
        type: Array,
        default: () => coursesData,
    },
});

const search = ref('');

const filteredCourses = computed(() => {
    const query = search.value.trim().toLowerCase();

    return props.courses.filter((material) =>
        [material.title, material.description, material.category]
            .join(' ')
            .toLowerCase()
            .includes(query)
    );
});

function handleStreakBadgeClick() {
    window.dispatchEvent(
        new CustomEvent('open-streak-broken', {
            detail: {
                previous_streak:
                    page.props.auth?.streak_broken?.previous_streak ||
                    (page.props.auth?.streak > 0 ? page.props.auth?.streak : 3),
                longest_streak: page.props.auth?.streak_broken?.longest_streak || 5,
            },
        })
    );
}
</script>
<template>
    <Head title="Beranda" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-foreground">Beranda</h2>
        </template>

        <div class="px-4 pt-6 pb-12 sm:px-6 lg:px-8">
            <div class="max-w-[76rem] mx-auto space-y-8">
                <div
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
                        <h1 class="font-bold text-md md:text-4xl text-primary">
                            Halo {{ $page.props.auth.user.name }} 👋
                        </h1>
                        <p class="mt-2 text-sm font-medium text-gray-600 md:mt-3 md:text-base">
                            Yuk, semangat belajar hari ini! Setiap langkah kecil bikin kamu makin
                            pintar. Kamu pasti bisa!
                        </p>
                        <div
                            v-if="$page.props.auth.user?.role === 'child'"
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 mt-3 border rounded-full text-xs md:text-sm font-bold transition-all duration-300 cursor-pointer select-none hover:shadow-md hover:scale-[1.02] active:scale-[0.98]"
                            :class="
                                $page.props.auth.streak_active_today
                                    ? 'bg-orange-100 border-orange-200 text-orange-700 shadow-sm'
                                    : 'bg-gray-100 border-gray-200 text-gray-600 hover:bg-gray-200/70'
                            "
                            @click="handleStreakBadgeClick"
                            role="button"
                            tabindex="0"
                            title="Klik untuk melihat status api belajar"
                        >
                            <img
                                :src="$page.props.auth.streak_active_today ? '/images/ui/streak.png' : '/images/ui/not streak.png'"
                                alt="Streak"
                                class="object-contain w-5 h-5 transition-transform duration-200 group-hover:scale-110"
                            />
                            <span v-if="$page.props.auth.streak_active_today">
                                {{ $page.props.auth.streak }} Hari Streak Aktif — Sudah Belajar Hari Ini 🔥
                            </span>
                            <span v-else-if="($page.props.auth.streak ?? 0) > 0">
                                {{ $page.props.auth.streak }} Hari Streak — Belum Belajar Hari Ini, Yuk Lanjut!
                            </span>
                            <span v-else>
                                Streak Belum Aktif — Belajar Hari Ini untuk Menyalakan Api!
                            </span>
                        </div>
                    </div>
                    <img
                        src="/images/brand/maskot.png"
                        class="w-24 h-full py-4 mr-6 md:w-36 md:mr-12"
                    />
                </div>

                <section class="space-y-4" aria-labelledby="courses-heading">
                    <div>
                        <div class="relative flex-1 max-w-md">
                            <PhMagnifyingGlass
                                class="absolute -translate-y-1/2 left-3 top-1/2 text-muted-foreground"
                                :size="18"
                                aria-hidden="true"
                            />
                            <Input
                                v-model="search"
                                type="search"
                                placeholder="Cari materi..."
                                class="pl-10 focus-visible:border-primary focus-visible:ring-transparent"
                                aria-label="Cari materi"
                            />
                        </div>
                    </div>

                    <div
                        v-if="filteredCourses.length"
                        class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <Card
                            v-for="material in filteredCourses"
                            :key="material.id"
                            class="flex flex-col p-0 overflow-hidden transition-shadow shadow-md ring-0"
                        >
                            <div class="relative aspect-[16/9] overflow-hidden bg-muted">
                                <img
                                    :src="material.thumbnail || '/images/ui/school.jpg'"
                                    :alt="`Thumbnail ${material.title}`"
                                    class="object-cover w-full h-full"
                                />
                            </div>
                            <div class="flex flex-col flex-1 px-5">
                                <h3 class="text-lg font-bold text-foreground">
                                    {{ material.title }}
                                </h3>
                                <p class="text-sm leading-6 text-muted-foreground">
                                    {{ material.description }}
                                </p>
                            </div>
                            <div class="flex items-center p-5 pt-0">
                                <Button :as-child="true" class="text-white">
                                    <Link :href="`/dashboard/courses/${material.id}`">
                                        Pelajari
                                        <PhArrowRight class="w-4 h-4 ml-1" aria-hidden="true" />
                                    </Link>
                                </Button>
                            </div>
                        </Card>
                    </div>

                    <div
                        v-else
                        class="px-6 py-12 text-center border border-dashed rounded-lg border-border bg-card"
                    >
                        <PhMagnifyingGlass
                            class="mx-auto text-muted-foreground"
                            :size="28"
                            aria-hidden="true"
                        />
                        <h3 class="mt-3 font-semibold text-foreground">Materi tidak ditemukan</h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Coba kata kunci yang berbeda.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
