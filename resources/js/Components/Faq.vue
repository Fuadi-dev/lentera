<script setup>
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/Components/ui/accordion';

const frequentlyAskedQuestions = [
    {
        question: 'Apa itu Lentera?',
        answer:
            'Lentera adalah platform belajar interaktif yang membantu anak Sekolah Dasar belajar secara mandiri, menyenangkan, dan inklusif dari rumah.',
    },
    {
        question: 'Untuk siapa Lentera dibuat?',
        answer:
            'Lentera dirancang untuk anak Sekolah Dasar, termasuk anak dengan kebutuhan belajar khusus seperti anak tuli, tunanetra, dan disleksia.',
    },
    {
        question: 'Apakah Lentera bisa digunakan tanpa pendampingan terus-menerus?',
        answer:
            'Bisa. Aktivitas belajar Lentera dibuat agar anak dapat mengikuti materi sesuai ritmenya, sementara orang tua tetap dapat memantau progres belajar.',
    },
    {
        question: 'Pelajaran apa saja yang tersedia?',
        answer:
            'Lentera menyediakan materi dan latihan untuk pelajaran dasar seperti Matematika, Bahasa Indonesia, dan Bahasa Inggris yang dikemas secara interaktif.',
    },
    {
        question: 'Apakah Lentera gratis digunakan?',
        answer:
            'Lentera menyediakan pengalaman belajar yang dapat dicoba secara gratis. Informasi lebih lanjut mengenai fitur dan akses tersedia saat pendaftaran.',
    },
];
</script>

<template>
    <section id="faq" class="py-20 bg-background sm:py-24">
        <div class="w-full max-w-3xl px-4 mx-auto sm:px-6">
            <div class="text-center">
                <p class="text-sm font-semibold tracking-wide uppercase text-primary">FAQ</p>
                <h2
                    class="mt-3 text-3xl font-bold leading-tight tracking-tight text-foreground sm:text-4xl"
                >
                    Pertanyaan yang sering ditanyakan
                </h2>
                <p class="max-w-2xl mx-auto mt-5 text-lg text-muted-foreground">
                    Temukan jawaban singkat tentang Lentera dan pengalaman belajar yang kami
                    hadirkan.
                </p>
            </div>

            <Accordion type="single" collapsible class="mt-10">
                <AccordionItem
                    v-for="(item, index) in frequentlyAskedQuestions"
                    :key="item.question"
                    :value="`faq-${index}`"
                    class="border-border"
                >
                    <AccordionTrigger class="py-5 text-base font-semibold text-foreground">
                        {{ item.question }}
                    </AccordionTrigger>
                    <AccordionContent class="text-base leading-relaxed text-muted-foreground">
                        {{ item.answer }}
                    </AccordionContent>
                </AccordionItem>
            </Accordion>
        </div>
    </section>
</template>
