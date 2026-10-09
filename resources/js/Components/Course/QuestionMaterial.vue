<script setup>
import { ref } from 'vue';
import { DragDropProvider } from '@dnd-kit/vue';
import { move } from '@dnd-kit/helpers';
import QuestionDragItem from '@/Components/Course/QuestionDragItem.vue';
import SortableQuestionDragItem from '@/Components/Course/SortableQuestionDragItem.vue';
import QuestionDropZone from '@/Components/Course/QuestionDropZone.vue';
import ChoiceDropZone from '@/Components/Course/ChoiceDropZone.vue';
import Button from '@/Components/ui/button/Button.vue';
import Card from '@/Components/ui/card/Card.vue';
import CardContent from '@/Components/ui/card/CardContent.vue';
import CardDescription from '@/Components/ui/card/CardDescription.vue';
import CardFooter from '@/Components/ui/card/CardFooter.vue';
import CardHeader from '@/Components/ui/card/CardHeader.vue';
import CardTitle from '@/Components/ui/card/CardTitle.vue';

const props = defineProps({
    material: { type: Object, required: true },
});

const answers = ref({
    [props.material.id]: {
        ordering:
            props.material.mode === 'ordering'
                ? (props.material.items ?? props.material.options ?? []).map((item) => item.id)
                : [],
    },
});
const submitted = ref(false);

function questionItems(question) {
    return question.items ?? question.options ?? [];
}

function questionZones(question) {
    return question.dropZones ?? [{ id: 'answer', label: 'Jawaban' }];
}

function questionAnswer(questionId, zoneId) {
    return answers.value[questionId]?.[zoneId] ?? [];
}

function isOrderingQuestion(question) {
    return question.mode === 'ordering';
}

function orderingAnswer(question) {
    return Array.isArray(question.answer) ? question.answer : (question.answer?.ordering ?? []);
}

function isOrderingCorrect(question) {
    const actualAnswer = questionAnswer(question.id, 'ordering');
    const expectedAnswer = orderingAnswer(question);

    return (
        actualAnswer.length === expectedAnswer.length &&
        actualAnswer.every((itemId, index) => itemId === expectedAnswer[index])
    );
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
                        <div>
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
            <p v-if="submitted" class="text-sm font-semibold">
                <template v-if="isOrderingQuestion(material)">
                    {{
                        isOrderingCorrect(material)
                            ? (material.explanation ?? 'Urutan jawaban benar.')
                            : 'Urutan belum tepat. Coba periksa kembali.'
                    }}
                </template>
                <template v-else>
                    {{ material.explanation ?? 'Jawaban sudah diperiksa.' }}
                </template>
            </p>
            <Button type="button" @click="submitted = true">
                {{ submitted ? 'Periksa lagi' : 'Periksa jawaban' }}
            </Button>
        </CardFooter>
    </Card>
</template>
