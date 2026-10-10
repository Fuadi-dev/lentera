<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import {
    PhBookOpen,
    PhCalendarCheck,
    PhCaretRight,
    PhFire,
    PhTrendUp,
} from '@phosphor-icons/vue';
import Card from '@/Components/ui/card/Card.vue';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/Components/ui/collapsible';

defineProps({
    children: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Dashboard Orang Tua" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-foreground">Dashboard Orang Tua</h2>
        </template>

        <div class="px-4 pt-6 pb-12 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-[76rem] space-y-8">
                <section
                    class="relative flex items-center justify-between overflow-hidden border-2 bg-gray-50 sm:rounded-lg"
                >
                    <span
                        class="absolute right-32 top-5 select-none text-3xl twinkle md:right-56"
                        aria-hidden="true"
                        >⭐</span
                    >
                    <span
                        class="absolute bottom-6 right-24 select-none text-2xl twinkle md:right-44"
                        style="animation-delay: 0.8s"
                        aria-hidden="true"
                        >✨</span
                    >

                    <div class="p-6 md:p-10">
                        <h1 class="text-md font-bold text-primary md:text-4xl">
                            Halo {{ $page.props.auth.user.name }} 👋
                        </h1>
                        <p class="mt-2 text-sm font-medium text-gray-600 md:mt-3 md:text-base">
                            Pantau terus perkembangan belajar anakmu. Setiap dukungan kecil dari
                            orang tua berarti besar untuk perjalanan mereka!
                        </p>
                    </div>
                    <span
                        class="absolute inset-y-0 right-0 w-1/3 bg-gradient-to-l from-primary/10 to-transparent"
                        aria-hidden="true"
                    ></span>
                    <img
                        src="/images/brand/maskot.png"
                        alt="Maskot Lentera"
                        class="relative z-10 mr-6 h-full w-24 py-4 md:mr-12 md:w-36"
                    />
                </section>

                <Collapsible
                    v-for="child in children"
                    :key="child.name"
                    as-child
                    :default-open="children.length <= 2 || children.indexOf(child) === 0"
                    :disabled="children.length <= 2"
                    class="group/child space-y-4"
                >
                    <section :aria-labelledby="`progress-${child.name}`">
                        <CollapsibleTrigger as-child>
                            <div
                                class="flex w-full cursor-pointer flex-wrap items-center justify-between gap-4 rounded-2xl border border-border bg-card px-5 py-4 text-left shadow-sm transition-colors hover:border-primary/40 data-[state=open]:rounded-b-lg md:px-6"
                                :class="{ 'cursor-default hover:border-border': children.length <= 2 }"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary text-sm font-bold text-primary-foreground shadow-sm"
                                        aria-hidden="true"
                                    >
                                        {{ child.avatar }}
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-medium uppercase tracking-wide text-muted-foreground"
                                        >
                                            Progress anak
                                        </p>
                                        <h2
                                            :id="`progress-${child.name}`"
                                            class="mt-0.5 text-xl font-bold text-foreground"
                                        >
                                            {{ child.name }}
                                        </h2>
                                        <p class="text-xs text-muted-foreground">
                                            {{ child.class }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="rounded-full bg-primary/10 px-3 py-1.5 text-xs font-semibold text-primary"
                                    >
                                        Aktif belajar
                                    </div>
                                    <PhCaretRight
                                        v-if="children.length > 2"
                                        class="size-5 text-muted-foreground transition-transform group-data-[state=open]/child:rotate-90"
                                        aria-hidden="true"
                                    />
                                </div>
                            </div>
                        </CollapsibleTrigger>

                        <CollapsibleContent class="space-y-4 pt-4">
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <Card class="min-h-[158px] border border-border p-4 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <p
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                        >
                                            Progress keseluruhan
                                        </p>
                                        <span class="rounded-lg bg-primary/10 p-2 text-primary">
                                            <PhTrendUp :size="18" aria-hidden="true" />
                                        </span>
                                    </div>
                                    <p class="mt-4 text-3xl font-bold tracking-tight text-foreground">
                                        {{ child.overallProgress }}%
                                    </p>
                                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-primary/20">
                                        <div
                                            class="h-full rounded-full bg-primary"
                                            :style="{ width: `${child.overallProgress}%` }"
                                        ></div>
                                    </div>
                                </Card>
                                <Card class="min-h-[158px] border border-border p-4 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <p
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                        >
                                            Materi selesai
                                        </p>
                                        <span class="rounded-lg bg-primary/10 p-2 text-primary">
                                            <PhBookOpen :size="18" aria-hidden="true" />
                                        </span>
                                    </div>
                                    <p class="mt-4 text-3xl font-bold tracking-tight text-foreground">
                                        {{ child.completedLessons }}/{{ child.totalLessons }}
                                    </p>
                                    <p class="mt-2 text-xs text-muted-foreground">
                                        materi pembelajaran
                                    </p>
                                </Card>
                                <Card class="min-h-[158px] border border-border p-4 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <p
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                        >
                                            Streak belajar
                                        </p>
                                        <span class="rounded-lg bg-primary/10 p-2 text-primary">
                                            <PhFire :size="18" aria-hidden="true" />
                                        </span>
                                    </div>
                                    <p class="mt-4 text-3xl font-bold tracking-tight text-foreground">
                                        {{ child.streak }} hari
                                    </p>
                                    <p class="mt-2 text-xs text-muted-foreground">
                                        terus pertahankan!
                                    </p>
                                </Card>
                                <Card class="min-h-[158px] border border-border p-4 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <p
                                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                        >
                                            Aktivitas terakhir
                                        </p>
                                        <span class="rounded-lg bg-primary/10 p-2 text-primary">
                                            <PhCalendarCheck :size="18" aria-hidden="true" />
                                        </span>
                                    </div>
                                    <p
                                        class="mt-4 line-clamp-2 text-sm font-semibold leading-5 text-foreground"
                                    >
                                        {{ child.lastActivity }}
                                    </p>
                                    <p class="mt-2 text-xs text-muted-foreground">Baru saja</p>
                                </Card>
                            </div>

                            <Card class="border border-border p-4 shadow-sm md:p-5">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-bold text-foreground md:text-lg">
                                            Progress per mata pelajaran
                                        </h3>
                                        <p class="mt-1 text-xs text-muted-foreground md:text-sm">
                                            Perkembangan belajar {{ child.name }} saat ini
                                        </p>
                                    </div>
                                    <span class="rounded-lg bg-muted p-2 text-muted-foreground">
                                        <PhBookOpen :size="20" aria-hidden="true" />
                                    </span>
                                </div>
                                <div class="mt-6 grid gap-x-8 gap-y-5 md:grid-cols-2">
                                    <div v-for="subject in child.subjects" :key="subject.name">
                                        <div class="mb-2 flex justify-between text-sm">
                                            <span class="font-medium text-foreground">{{
                                                subject.name
                                            }}</span>
                                            <span class="font-semibold text-muted-foreground"
                                                >{{ subject.progress }}%</span
                                            >
                                        </div>
                                        <div class="h-2.5 overflow-hidden rounded-full bg-muted">
                                            <div
                                                class="h-full rounded-full transition-all duration-500"
                                                :class="subject.color"
                                                :style="{ width: `${subject.progress}%` }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </Card>
                        </CollapsibleContent>
                    </section>
                </Collapsible>

                <div
                    v-if="!children.length"
                    class="rounded-xl border border-dashed border-border bg-card px-6 py-12 text-center"
                >
                    <h2 class="font-semibold text-foreground">Data anak belum tersedia</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Data progress akan tampil setelah anak terhubung ke akun orang tua.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
