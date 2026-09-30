<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { PhArrowRight, PhMagnifyingGlass } from '@phosphor-icons/vue';
import Button from '@/Components/ui/button/Button.vue';
import Card from '@/Components/ui/card/Card.vue';
import Input from '@/Components/ui/input/Input.vue';
import coursesData from '../../../data/courses.json';

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
</script>

<template>
    <Head title="Beranda" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-foreground">Dashboard</h2>
        </template>

        <div class="px-4 pt-6 pb-12 sm:px-6 lg:px-8">
            <div class="mx-auto space-y-8 max-w-7xl">
                <section class="p-6 border border-orange-200 rounded-xl bg-orange-50 sm:p-8">
                    <p class="text-sm font-semibold tracking-wide uppercase text-primary">
                        Ruang belajar
                    </p>
                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Halo {{ $page.props.auth.user.name }} 👋
                    </h1>
                    <p class="max-w-2xl mt-2 text-sm text-muted-foreground sm:text-base">
                        Yuk, semangat belajar hari ini! Setiap langkah kecil bikin kamu makin
                        pintar. Kamu pasti bisa!
                    </p>
                </section>

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
                                class="pl-10"
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
                            class="flex flex-col overflow-hidden transition-shadow hover:shadow-md"
                        >
                            <div class="relative aspect-[16/9] overflow-hidden bg-muted">
                                <img
                                    :src="material.thumbnail || '/school.jpg'"
                                    :alt="`Thumbnail ${material.title}`"
                                    class="object-cover w-full h-full transition-transform duration-300 hover:scale-105"
                                />
                            </div>
                            <div class="flex flex-col flex-1 p-5">
                                <h3 class="mt-1 text-lg font-bold text-foreground">
                                    {{ material.title }}
                                </h3>
                                <p class="mt-2 text-sm leading-6 text-muted-foreground">
                                    {{ material.description }}
                                </p>
                            </div>
                            <div class="flex items-center p-5 pt-0">
                                <Button :as-child="true">
                                    <Link :href="`/dashboard/courses/${material.id}`">
                                        Lihat Materi
                                    </Link>
                                    <PhArrowRight class="w-4 h-4 ml-1 -mr-1" aria-hidden="true" />
                                </Button>
                            </div>
                        </Card>
                    </div>

                    <div
                        v-else
                        class="px-6 py-12 text-center border border-dashed rounded-lg border-border bg-card"
                    >
                        <MagnifyingGlass
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
