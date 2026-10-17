<script setup>
import { onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { PhArrowLeft } from '@phosphor-icons/vue';
import { Headphones } from 'lucide-vue-next';
import Button from '@/Components/ui/button/Button.vue';
import Card from '@/Components/ui/card/Card.vue';
import CardContent from '@/Components/ui/card/CardContent.vue';
import CardDescription from '@/Components/ui/card/CardDescription.vue';
import CardFooter from '@/Components/ui/card/CardFooter.vue';
import CardHeader from '@/Components/ui/card/CardHeader.vue';
import CardTitle from '@/Components/ui/card/CardTitle.vue';
import NextMaterialButton from '@/Components/Course/NextMaterialButton.vue';
import MarkUnitDoneButton from '@/Components/Course/MarkUnitDoneButton.vue';

const props = defineProps({
    course: { type: Object, required: true },
    classNumber: { type: [Number, String], required: true },
    material: { type: Object, required: true },
    nextUnitHref: { type: String, default: null },
    nextUnitTitle: { type: String, default: '' },
    completed: { type: Boolean, default: false },
});

function speakText() {
    if (!('speechSynthesis' in window)) {
        alert('Fitur suara belum tersedia di browser ini.');
        return;
    }

    window.speechSynthesis.cancel();
    const textToSpeak = `${props.material.title}. ${props.material.content}`;
    const utterance = new SpeechSynthesisUtterance(textToSpeak);
    utterance.lang = 'id-ID';
    window.speechSynthesis.speak(utterance);
}

onBeforeUnmount(() => {
    window.speechSynthesis?.cancel();
});
</script>

<template>
    <Card class="w-full overflow-hidden shadow-sm bg-white rounded-3xl border border-slate-100">
        <CardHeader class="p-6 sm:p-8 bg-white border-b border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <CardTitle class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ material.title }}
                    </CardTitle>
                    <CardDescription class="text-sm text-slate-500 font-medium">
                        Baca materi berikut dengan saksama.
                    </CardDescription>
                </div>

                <Button
                    type="button"
                    variant="outline"
                    class="gap-2 rounded-xl font-semibold border-slate-200 text-slate-700 hover:text-slate-900 hover:bg-slate-50 cursor-pointer self-start sm:self-auto"
                    @click="speakText"
                    title="Dengarkan materi pembelajaran"
                >
                    <Headphones :size="17" class="text-primary shrink-0" aria-hidden="true" />
                    <span>Dengarkan Materi</span>
                </Button>
            </div>
        </CardHeader>

        <CardContent class="p-6 sm:p-8">
            <div class="prose prose-slate max-w-none text-base sm:text-lg leading-relaxed sm:leading-loose text-slate-800 font-normal">
                <p>{{ material.content }}</p>
            </div>
        </CardContent>

        <CardFooter class="p-6 sm:p-8 pt-6 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <Button as-child variant="outline" class="gap-2 rounded-xl border-slate-200 text-slate-700 hover:text-slate-900 hover:bg-white font-semibold">
                <Link
                    :href="
                        route('courses.learning-path', { course: course.id, class: classNumber })
                    "
                >
                    <PhArrowLeft :size="16" weight="bold" aria-hidden="true" />
                    <span>Kembali ke jalur belajar</span>
                </Link>
            </Button>

            <div class="flex flex-wrap items-center gap-3 justify-end">
                <MarkUnitDoneButton :unit-id="material.id" :completed="completed" />
                <NextMaterialButton
                    v-if="nextUnitHref"
                    :href="nextUnitHref"
                    :title="nextUnitTitle"
                />
            </div>
        </CardFooter>
    </Card>
</template>
