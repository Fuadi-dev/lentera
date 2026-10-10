<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import confetti from 'canvas-confetti';
import {
    Check,
    CircleHelp,
    Flame,
    Headphones,
    LockKeyhole,
    RotateCcw,
    Star,
    X,
} from 'lucide-vue-next';

const props = defineProps({
    unit: {
        type: Object,
        required: true,
    },
    chapterUnits: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['completed']);

const currentQuestionIndex = ref(0);
const currentScore = ref(0);
const displayedScore = ref(0);
const streak = ref(0);
const selectedOption = ref(null);
const textAnswer = ref('');
const answerState = ref(null);
const feedback = ref('');
const completed = ref(false);
const confettiTriggered = ref(false);
let scoreAnimationFrame = null;

const questions = computed(() => props.unit.questions ?? []);
const currentQuestion = computed(() => questions.value[currentQuestionIndex.value]);
const passingScore = computed(() => props.unit.passingScore ?? 0);
const progressUnits = computed(() =>
    props.chapterUnits.length
        ? props.chapterUnits
        : [{ id: props.unit.id, title: props.unit.title, type: props.unit.type }]
);
const progressIndex = computed(() =>
    progressUnits.value.findIndex((item) => item.id === props.unit.id)
);
const progressPosition = computed(() =>
    progressIndex.value >= 0 ? progressIndex.value : 0
);
const isLastQuestion = computed(
    () => currentQuestionIndex.value === questions.value.length - 1
);

function optionCount(option) {
    const value = Number(option.text);

    return Number.isInteger(value) && value > 0 ? Math.min(value, 20) : 0;
}

function animateScore(target) {
    cancelAnimationFrame(scoreAnimationFrame);
    const start = displayedScore.value;
    const startedAt = performance.now();

    function update(now) {
        const progress = Math.min((now - startedAt) / 500, 1);
        displayedScore.value = Math.round(start + (target - start) * progress);

        if (progress < 1) {
            scoreAnimationFrame = requestAnimationFrame(update);
        }
    }

    scoreAnimationFrame = requestAnimationFrame(update);
}

function triggerConfetti() {
    if (confettiTriggered.value || currentScore.value < passingScore.value) {
        return;
    }

    confettiTriggered.value = true;
    confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.65 },
        colors: ['#16a34a', '#f59e0b', '#2563eb'],
    });
}

function announceCompletion() {
    if (completed.value) {
        return;
    }

    completed.value = true;
    triggerConfetti();
    emit('completed', {
        score: currentScore.value,
        passingScore: passingScore.value,
        passed: currentScore.value >= passingScore.value,
    });
}

function chooseOption(option) {
    if (answerState.value === 'correct') {
        return;
    }

    selectedOption.value = option.id;

    const expectedAnswers = Array.isArray(currentQuestion.value.answer)
        ? currentQuestion.value.answer
        : [currentQuestion.value.answer];
    
    const isCorrect = expectedAnswers.includes(option.id);

    if (isCorrect) {
        answerState.value = 'correct';
        streak.value += 1;
        currentScore.value += currentQuestion.value.points ?? 0;
        animateScore(currentScore.value);
        feedback.value = `Benar! Kamu mendapat ${currentQuestion.value.points ?? 0} poin.`;
        triggerConfetti();
    } else {
        answerState.value = 'incorrect';
        streak.value = 0;
        feedback.value = `Belum tepat. ${currentQuestion.value.explanation ?? 'Coba lagi dengan teliti.'}`;
    }
}

function submitTextAnswer() {
    if (!currentQuestion.value || !textAnswer.value.trim()) {
        feedback.value = 'Tulis jawabanmu terlebih dahulu, ya.';
        return;
    }

    const expectedAnswers = Array.isArray(currentQuestion.value.answer)
        ? currentQuestion.value.answer
        : [currentQuestion.value.answer];
    const normalizedAnswer = textAnswer.value.trim().toLowerCase();
    const isCorrect = expectedAnswers.some(
        (answer) => String(answer).trim().toLowerCase() === normalizedAnswer
    );

    if (isCorrect) {
        answerState.value = 'correct';
        streak.value += 1;
        currentScore.value += currentQuestion.value.points ?? 0;
        animateScore(currentScore.value);
        feedback.value = `Benar! Kamu mendapat ${currentQuestion.value.points ?? 0} poin.`;
        triggerConfetti();
    } else {
        answerState.value = 'incorrect';
        streak.value = 0;
        feedback.value = `Belum tepat. ${currentQuestion.value.explanation ?? 'Coba lagi dengan teliti.'}`;
    }
}

function continueQuiz() {
    if (isLastQuestion.value) {
        announceCompletion();
        return;
    }

    currentQuestionIndex.value += 1;
    selectedOption.value = null;
    textAnswer.value = '';
    answerState.value = null;
    feedback.value = '';
}

function retryQuestion() {
    selectedOption.value = null;
    textAnswer.value = '';
    answerState.value = null;
    feedback.value = '';
}

function speakQuestion() {
    if (!('speechSynthesis' in window) || !currentQuestion.value) {
        feedback.value = 'Fitur suara belum tersedia di browser ini.';
        return;
    }

    window.speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(currentQuestion.value.question);
    utterance.lang = 'id-ID';
    window.speechSynthesis.speak(utterance);
}

onBeforeUnmount(() => {
    cancelAnimationFrame(scoreAnimationFrame);
    window.speechSynthesis?.cancel();
});
</script>

<template>
    <section
        class="space-y-6 rounded-2xl border border-border bg-card p-4 text-card-foreground shadow-md sm:p-6"
        aria-labelledby="quiz-activity-title"
    >
        <header class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-primary">Aktivitas belajar</p>
                <h2 id="quiz-activity-title" class="mt-1 text-2xl font-bold">
                    {{ unit.title }}
                </h2>
            </div>
            <div
                class="flex items-center gap-2 rounded-full bg-amber-100 px-4 py-2 font-bold text-amber-900"
                aria-label="Total poin"
            >
                <Star :size="18" fill="currentColor" aria-hidden="true" />
                <span>{{ displayedScore }} poin</span>
            </div>
        </header>

        <div
            class="flex items-center gap-2 overflow-x-auto pb-2"
            aria-label="Progress aktivitas dalam chapter"
        >
            <template v-for="(progressUnit, index) in progressUnits" :key="progressUnit.id">
                <div
                    class="flex size-9 shrink-0 items-center justify-center rounded-full border-2 text-sm font-bold"
                    :class="
                        index < progressPosition
                            ? 'border-green-600 bg-green-600 text-white'
                            : index === progressPosition
                              ? 'animate-pulse border-primary bg-primary text-primary-foreground'
                              : 'border-border bg-muted text-muted-foreground'
                    "
                    :aria-label="`${progressUnit.title}, ${index === progressPosition ? 'sedang dikerjakan' : index < progressPosition ? 'selesai' : 'terkunci'}`"
                >
                    <Check v-if="index < progressPosition" :size="16" aria-hidden="true" />
                    <LockKeyhole v-else-if="index > progressPosition" :size="14" aria-hidden="true" />
                    <span v-else>{{ index + 1 }}</span>
                </div>
                <div
                    v-if="index < progressUnits.length - 1"
                    class="h-0.5 min-w-6 flex-1"
                    :class="index < progressPosition ? 'bg-green-600' : 'bg-border'"
                    aria-hidden="true"
                ></div>
            </template>
        </div>

        <div v-if="streak > 1" class="inline-flex items-center gap-2 rounded-full bg-orange-100 px-3 py-1 text-sm font-semibold text-orange-900">
            <Flame :size="17" fill="currentColor" aria-hidden="true" />
            {{ streak }} jawaban benar berturut-turut
        </div>

        <div v-if="currentQuestion" class="space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-muted-foreground">
                    Pertanyaan {{ currentQuestionIndex + 1 }} dari {{ questions.length }}
                </p>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-md border border-border px-3 py-2 text-sm font-semibold hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    @click="speakQuestion"
                >
                    <Headphones :size="17" aria-hidden="true" />
                    Dengarkan Soal
                </button>
            </div>

            <div class="flex items-start gap-3">
                <CircleHelp class="mt-1 shrink-0 text-primary" :size="24" aria-hidden="true" />
                <h3 class="text-xl font-semibold leading-relaxed">{{ currentQuestion.question }}</h3>
            </div>

            <div v-if="currentQuestion.type === 'multiple_choice'" class="grid gap-3 sm:grid-cols-2">
                <button
                    v-for="option in currentQuestion.options"
                    :key="option.id"
                    type="button"
                    class="relative min-h-40 rounded-xl border-2 p-4 text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
                    :class="{
                        'border-green-600 bg-green-50 text-green-950': selectedOption === option.id && answerState === 'correct',
                        'animate-[shake_250ms_ease-in-out] border-amber-500 bg-amber-50 text-amber-950': selectedOption === option.id && answerState === 'incorrect',
                        'border-border bg-background hover:-translate-y-1 hover:border-primary hover:shadow-md': selectedOption !== option.id,
                    }"
                    :aria-label="`Jawaban ${option.text}, ${optionCount(option)} kotak`"
                    @click="chooseOption(option)"
                >
                    <span class="flex items-start justify-between gap-3">
                        <span class="text-4xl font-black">{{ option.text }}</span>
                        <Check
                            v-if="selectedOption === option.id && answerState === 'correct'"
                            class="animate-[scale-in_200ms_ease-out]"
                            :size="28"
                            aria-label="Jawaban benar"
                        />
                        <X
                            v-else-if="selectedOption === option.id && answerState === 'incorrect'"
                            :size="28"
                            aria-label="Jawaban belum tepat"
                        />
                    </span>
                    <span class="mt-4 grid grid-cols-5 gap-1" aria-hidden="true">
                        <span
                            v-for="box in optionCount(option)"
                            :key="box"
                            class="size-4 rounded-sm bg-primary/70"
                        ></span>
                    </span>
                </button>
            </div>

            <div v-if="currentQuestion.type === 'short_answer'" class="space-y-3">
                <label for="quiz-answer" class="block font-semibold">
                    Tulis jawabanmu
                </label>
                <input
                    id="quiz-answer"
                    v-model="textAnswer"
                    type="text"
                    inputmode="numeric"
                    autocomplete="off"
                    class="w-full rounded-xl border-2 border-border bg-background px-4 py-4 text-xl font-bold text-foreground outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/30"
                    :aria-label="`Jawaban untuk ${currentQuestion.question}`"
                    :disabled="answerState === 'correct'"
                    @keyup.enter="submitTextAnswer"
                />
                <button
                    v-if="answerState !== 'correct'"
                    type="button"
                    class="rounded-md bg-primary px-4 py-2 font-semibold text-primary-foreground hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    @click="submitTextAnswer"
                >
                    Periksa jawaban
                </button>
            </div>

            <div
                class="min-h-12 rounded-lg border p-3 text-sm leading-relaxed"
                :class="
                    answerState === 'correct'
                        ? 'border-green-600 bg-green-50 text-green-950'
                        : answerState === 'incorrect'
                          ? 'border-amber-500 bg-amber-50 text-amber-950'
                          : 'border-transparent'
                "
                aria-live="polite"
            >
                <p v-if="feedback" class="flex items-start gap-2">
                    <Check v-if="answerState === 'correct'" :size="18" class="mt-0.5 shrink-0" aria-hidden="true" />
                    <X v-else-if="answerState === 'incorrect'" :size="18" class="mt-0.5 shrink-0" aria-hidden="true" />
                    <span>{{ feedback }}</span>
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <button
                    v-if="answerState === 'incorrect'"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-md border border-border px-4 py-2 font-semibold hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    @click="retryQuestion"
                >
                    <RotateCcw :size="17" aria-hidden="true" />
                    Coba lagi
                </button>
                <button
                    v-if="answerState === 'correct'"
                    type="button"
                    class="rounded-md bg-primary px-4 py-2 font-semibold text-primary-foreground hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    @click="continueQuiz"
                >
                    {{ isLastQuestion ? 'Selesai' : 'Pertanyaan berikutnya' }}
                </button>
            </div>
        </div>

        <div v-else class="rounded-lg border border-green-600 bg-green-50 p-4 text-green-950" aria-live="polite">
            Aktivitas ini belum memiliki pertanyaan.
        </div>
    </section>
</template>

<style scoped>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-3px); }
    75% { transform: translateX(3px); }
}

@keyframes scale-in {
    from { transform: scale(0.5); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>
