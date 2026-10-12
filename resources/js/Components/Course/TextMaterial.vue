<script setup>
import { onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { PhArrowLeft, PhArrowRight } from '@phosphor-icons/vue';
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
    <Card class="w-full h-full overflow-hidden shadow-md ring-transparent">
        <CardHeader class="bg-white">
            <div class="flex items-center justify-between gap-3">
                <CardTitle class="text-2xl">{{ material.title }}</CardTitle>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-md border border-border px-3 py-2 text-sm font-semibold hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    @click="speakText"
                >
                    <Headphones :size="17" aria-hidden="true" />
                    Dengarkan Teks
                </button>
            </div>
            <CardDescription>Baca materi berikut dengan saksama.</CardDescription>
        </CardHeader>
        <CardContent>
            <p class="text-base leading-8 text-foreground">{{ material.content }}</p>
        </CardContent>
        <CardFooter class="justify-between">
            <div class="flex gap-3">
                <Button :as-child="true" class="text-white">
                <Link
                    :href="
                        route('courses.learning-path', { course: course.id, class: classNumber })
                    "
                >
                <PhArrowLeft :size="16" aria-hidden="true" />
                    Kembali ke jalur belajar
                </Link>
                </Button>
                <MarkUnitDoneButton :unit-id="material.id" :completed="completed" />
            </div>
            <NextMaterialButton
                v-if="nextUnitHref"
                :href="nextUnitHref"
                :title="nextUnitTitle"
            />
        </CardFooter>
    </Card>
</template>
