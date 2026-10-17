<script setup>
import { BookOpen, CheckCircle2, Flame, GraduationCap } from 'lucide-vue-next';

const steps = [
    {
        number: '01',
        title: 'Pilih Mata Pelajaran',
        description: 'Pilih materi yang ingin dipelajari langsung dari katalog dashboard.',
        icon: BookOpen,
    },
    {
        number: '02',
        title: 'Tentukan Tingkat Kelas',
        description: 'Tentukan jenjang kelas yang sesuai dengan tahapan belajar anak.',
        icon: GraduationCap,
    },
    {
        number: '03',
        title: 'Selesaikan Urutan Level',
        description: 'Tuntaskan tiap level materi agar akses ke level baru terbuka.',
        icon: CheckCircle2,
    },
    {
        number: '04',
        title: 'Pertahankan Api Belajar',
        description: 'Kerjakan minimal satu soal tiap hari agar streak belajar menyala.',
        icon: Flame,
    },
];
</script>

<template>
    <section id="panduan" class="py-20 bg-background sm:py-24">
        <div class="w-full max-w-6xl px-4 mx-auto sm:px-6">
            <div class="max-w-2xl panduan-header">
                <p class="text-sm font-semibold tracking-wide uppercase text-primary">
                    Panduan
                </p>
                <h2
                    class="mt-3 text-3xl font-bold leading-tight tracking-tight text-foreground sm:text-4xl"
                >
                    Cara Menggunakan Lentera
                </h2>
                <p class="mt-3 text-base text-muted-foreground">
                    Alur belajar mandiri yang runtut dan mudah dipahami anak.
                </p>
            </div>

            <div class="panduan-grid grid gap-5 mt-12 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="step in steps"
                    :key="step.number"
                    class="panduan-card flex flex-col justify-between h-full p-6 transition-colors border rounded-2xl border-border bg-white hover:border-primary/40"
                >
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center justify-center rounded-xl size-11 bg-primary/10 text-primary">
                                <component :is="step.icon" class="size-5" />
                            </div>
                            <span class="text-xs font-bold text-muted-foreground">
                                {{ step.number }}
                            </span>
                        </div>

                        <h3 class="text-lg font-bold leading-snug text-foreground">
                            {{ step.title }}
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                            {{ step.description }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
