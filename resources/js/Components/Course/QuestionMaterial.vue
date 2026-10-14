<script setup>
import { ref } from 'vue';
import { DragDropProvider } from '@dnd-kit/vue';
import { move } from '@dnd-kit/helpers';
import confetti from 'canvas-confetti';
import { RotateCcw } from 'lucide-vue-next';
import QuestionDragItem from '@/Components/Course/QuestionDragItem.vue';
import SortableQuestionDragItem from '@/Components/Course/SortableQuestionDragItem.vue';
import QuestionDropZone from '@/Components/Course/QuestionDropZone.vue';
import ChoiceDropZone from '@/Components/Course/ChoiceDropZone.vue';
import Button from '@/Components/ui/button/Button.vue';
import Card from '@/Components/ui/card/Card.vue';
import CardContent from '@/Components/ui/card/CardContent.vue';
import CardDescription from '@/Components/ui/card/CardDescription.vue';
import CardFooter from '@/Components/ui/card/CardFooter.vue';
import NextMaterialButton from '@/Components/Course/NextMaterialButton.vue';
import MarkUnitDoneButton from '@/Components/Course/MarkUnitDoneButton.vue';
import CardHeader from '@/Components/ui/card/CardHeader.vue';
import CardTitle from '@/Components/ui/card/CardTitle.vue';
import { useStreakActivity } from '@/Composables/useStreakActivity';

const props = defineProps({
    material: { type: Object, required: true },
    nextUnitHref: { type: String, default: null },
    nextUnitTitle: { type: String, default: '' },
    completed: { type: Boolean, default: false },
});

function isOrderingQuestion(question) {
    return question.mode === 'ordering' || Array.isArray(question.answer);
}

function getInitialOrderingItems() {
    return (props.material.items ?? props.material.options ?? []).map((item) => item.id);
}

const answers = ref({
    [props.material.id]: {
        ordering: isOrderingQuestion(props.material) ? getInitialOrderingItems() : [],
    },
});
const submitted = ref(false);

const { recordStreakActivity, markUnitDone, unitMarkedDone } = useStreakActivity();

function checkAnswer() {
    submitted.value = true;

    if (isAnswerCorrect(props.material)) {
        try {
            confetti({
                particleCount: 60,
                spread: 60,
                origin: { y: 0.7 },
                colors: ['#16a34a', '#22c55e', '#f59e0b'],
            });
        } catch {
            // ignore fallback
        }

        // Jawaban benar: otomatis tertandai selesai + streak hari ini.
        markUnitDone(props.material.id, props.completed);

        return;
    }

    // Jawaban salah: tetap dihitung untuk streak harian jika hari ini belum tercatat.
    recordStreakActivity();
}

function retryQuestion() {
    submitted.value = false;
    answers.value[props.material.id] = {
        ordering: isOrderingQuestion(props.material) ? getInitialOrderingItems() : [],
    };
}

function questionItems(question) {
    return question.items ?? question.options ?? [];
}

function questionZones(question) {
    return question.dropZones ?? [{ id: 'answer', label: 'Jawaban' }];
}

function questionAnswer(questionId, zoneId) {
    return answers.value[questionId]?.[zoneId] ?? [];
}

function orderingAnswer(question) {
    return Array.isArray(question.answer) ? question.answer : (question.answer?.ordering ?? []);
}

function isOrderingCorrect(question) {
    const actualAnswer = questionAnswer(question.id, 'ordering');
    const expectedAnswer = orderingAnswer(question);

    return (
        actualAnswer.length === expectedAnswer.length &&
        actualAnswer.every((itemId, index) => String(itemId) === String(expectedAnswer[index]))
    );
}

function isDropZoneCorrect(question) {
    const expectedAnswer = question.answer ?? {};
    const actualAnswer = answers.value[question.id] ?? {};
    const zones = Object.keys(expectedAnswer);

    if (zones.length === 0) {
        return false;
    }

    return zones.every((zoneId) => {
        const expectedItems = Array.isArray(expectedAnswer[zoneId])
            ? expectedAnswer[zoneId]
            : [expectedAnswer[zoneId]];
        const actualItems = actualAnswer[zoneId] ?? [];

        return (
            actualItems.length === expectedItems.length &&
            expectedItems.every((itemId) => actualItems.some((actual) => String(actual) === String(itemId)))
        );
    });
}

function isAnswerCorrect(question) {
    return isOrderingQuestion(question)
        ? isOrderingCorrect(question)
        : isDropZoneCorrect(question);
}

function isQuestionItemPlaced(questionId, itemId) {
    return Object.values(answers.value[questionId] ?? {}).some((items) => items.includes(itemId));
}

function handleDragEnd(event) {
    if (event.canceled) {
        return;
    }

    const sourceData = event.operation.source?.data;
    const targetData = event.operation.target?.data;

    if (!sourceData || !targetData || sourceData.questionId !== targetData.questionId) {
        return;
    }

    const questionAnswers = answers.value[sourceData.questionId] ?? {};

    if (isOrderingQuestion(props.material)) {
        questionAnswers.ordering = move(questionAnswers.ordering ?? [], event);
        answers.value[sourceData.questionId] = questionAnswers;

        return;
    }

    const currentZone = Object.keys(questionAnswers).find((zoneId) =>
        questionAnswers[zoneId].includes(sourceData.itemId)
    );

    if (sourceData.isPlaced && currentZone) {
        const itemIndex = questionAnswers[currentZone].indexOf(sourceData.itemId);

        questionAnswers[currentZone].splice(itemIndex, 1);
    }

    if (targetData.zoneId === 'source') {
        answers.value[sourceData.questionId] = questionAnswers;

        return;
    }

    questionAnswers[targetData.zoneId] = [
        ...(questionAnswers[targetData.zoneId] ?? []),
        sourceData.itemId,
    ];
    answers.value[sourceData.questionId] = questionAnswers;
}
</script>

<template>
    <Card class="h-full">
        <CardHeader>
            <CardTitle class="text-2xl">{{ material.title }}</CardTitle>
            <CardDescription>{{ material.question }}</CardDescription>
        </CardHeader>
        <CardContent>
            <DragDropProvider @dragEnd="handleDragEnd">
                <div class="space-y-6">
                    <template v-if="isOrderingQuestion(material)">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2 min-h-16">
                                <SortableQuestionDragItem
                                    v-for="(itemId, itemIndex) in questionAnswer(
                                        material.id,
                                        'ordering'
                                    )"
                                    :key="`ordering-${itemId}`"
                                    :id="`ordering-${itemId}`"
                                    :question-id="material.id"
                                    :item-id="itemId"
                                    :index="itemIndex"
                                    :label="
                                        questionItems(material).find((item) => item.id === itemId)
                                            ?.label ?? itemId
                                    "
                                    :image="
                                        questionItems(material).find((item) => item.id === itemId)
                                            ?.image
                                    "
                                />
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <ChoiceDropZone id="source" :question-id="material.id" label="Pilihan">
                            <QuestionDragItem
                                v-for="item in questionItems(material)"
                                v-show="
                                    item.repeatable || !isQuestionItemPlaced(material.id, item.id)
                                "
                                :key="item.id"
                                :id="item.id"
                                :question-id="material.id"
                                :label="item.label ?? item.text"
                                :image="item.image"
                            />
                        </ChoiceDropZone>
                        <div class="space-y-4">
                            <QuestionDropZone
                                v-for="zone in questionZones(material)"
                                :key="zone.id"
                                :id="zone.id"
                                :question-id="material.id"
                                :label="zone.label"
                            >
                                <QuestionDragItem
                                    v-for="(itemId, itemIndex) in questionAnswer(
                                        material.id,
                                        zone.id
                                    )"
                                    :key="`${itemId}-${itemIndex}`"
                                    :id="`${itemId}-${itemIndex}`"
                                    :question-id="material.id"
                                    :item-id="itemId"
                                    :label="
                                        questionItems(material).find((item) => item.id === itemId)
                                            ?.label ??
                                        questionItems(material).find((item) => item.id === itemId)
                                            ?.text ??
                                        itemId
                                    "
                                    :image="
                                        questionItems(material).find((item) => item.id === itemId)
                                            ?.image
                                    "
                                />
                            </QuestionDropZone>
                        </div>
                    </template>
                </div>
            </DragDropProvider>
        </CardContent>
        <CardFooter class="justify-between">
            <p
                v-if="submitted"
                class="text-sm font-semibold"
                :class="isAnswerCorrect(material) ? 'text-green-600' : 'text-amber-600'"
            >
                <template v-if="isOrderingQuestion(material)">
                    {{
                        isOrderingCorrect(material)
                            ? (material.explanation ?? 'Urutan jawaban benar.')
                            : 'Urutan belum tepat. Coba periksa kembali.'
                    }}
                </template>
                <template v-else>
                    {{
                        isDropZoneCorrect(material)
                            ? (material.explanation ?? 'Jawaban benar.')
                            : 'Belum tepat. Coba periksa kembali.'
                    }}
                </template>
            </p>
            <div class="flex gap-3">
                <Button
                    v-if="submitted && !isAnswerCorrect(material)"
                    type="button"
                    variant="outline"
                    class="gap-2"
                    @click="retryQuestion"
                >
                    <RotateCcw class="w-4 h-4" />
                    Coba lagi
                </Button>
                <Button
                    type="button"
                    :disabled="submitted && isAnswerCorrect(material)"
                    @click="checkAnswer"
                >
                    {{ submitted && isAnswerCorrect(material) ? 'Benar!' : 'Periksa jawaban' }}
                </Button>
                <MarkUnitDoneButton :unit-id="material.id" :completed="completed || unitMarkedDone" />
            </div>
            <NextMaterialButton
                v-if="nextUnitHref"
                :href="nextUnitHref"
                :title="nextUnitTitle"
            />
        </CardFooter>
    </Card>
</template>
