<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import VideoSummary from '@/Components/Course/VideoSummary.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    PhArrowLeft,
    PhArrowRight,
    PhBookOpen,
    PhCaretRight,
    PhCheckCircle,
    PhGraduationCap,
    PhListChecks,
    PhLock,
    PhVideoCamera,
} from '@phosphor-icons/vue';
import Button from '@/Components/ui/button/Button.vue';
import Card from '@/Components/ui/card/Card.vue';
import CardContent from '@/Components/ui/card/CardContent.vue';
import CardDescription from '@/Components/ui/card/CardDescription.vue';
import CardFooter from '@/Components/ui/card/CardFooter.vue';
import CardHeader from '@/Components/ui/card/CardHeader.vue';
import CardTitle from '@/Components/ui/card/CardTitle.vue';
import Input from '@/Components/ui/input/Input.vue';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/Components/ui/collapsible';
import {
    Sidebar,
    SidebarContent,
    SidebarGroup,
    SidebarGroupContent,
    SidebarHeader,
    SidebarInset,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    SidebarProvider,
} from '@/Components/ui/sidebar';

const props = defineProps({
    course: { type: Object, required: true },
    classNumber: { type: [Number, String], required: true },
    material: { type: Object, required: true },
});

const answers = ref({});
const submitted = ref(false);
const sidebarOpen = ref(true);

const courseClasses = computed(() => props.course.classes ?? []);

function classUnits(courseClass) {
    if (courseClass.section) {
        return courseClass.section.flatMap((section) => section.unit ?? []);
    }

    return (courseClass.levels ?? []).flatMap((level) =>
        (level.chapters ?? []).flatMap((chapter) => chapter.units ?? [])
    );
}

function classFinalTest(courseClass) {
    if (courseClass.finalTest) {
        return courseClass.finalTest;
    }

    return courseClass.levels?.[0]?.chapters?.[0]?.finalTest;
}

function isClassUnlocked(courseClass) {
    return courseClass.number <= Number(props.classNumber);
}

function unitIcon(type) {
    if (type === 'video') {
        return PhVideoCamera;
    }

    if (type === 'quiz') {
        return PhListChecks;
    }

    return PhBookOpen;
}

function unitHref(courseClass, unit) {
    return route('courses.material', {
        course: props.course.id,
        class: courseClass.number,
        material: unit.id,
    });
}

const materialIcon = computed(() => {
    if (props.material.type === 'video') {
        return PhVideoCamera;
    }

    if (props.material.type === 'quiz') {
        return PhListChecks;
    }

    return PhBookOpen;
});

const youtubeEmbedUrl = computed(() => {
    if (props.material.type !== 'video' || !props.material.url) {
        return null;
    }

    try {
        const url = new URL(props.material.url);
        let videoId =
            url.hostname === 'youtu.be' ? url.pathname.slice(1) : url.searchParams.get('v');

        if (url.hostname.includes('youtube.com') && url.pathname.startsWith('/embed/')) {
            videoId = url.pathname.split('/')[2];
        }

        return videoId ? `https://www.youtube.com/embed/${videoId}` : null;
    } catch {
        return null;
    }
});

const score = computed(() => {
    if (!submitted.value || props.material.type !== 'quiz') {
        return 0;
    }

    return (props.material.questions ?? []).reduce((total, question) => {
        const answer = answers.value[question.id]?.trim().toLowerCase();
        const expected = Array.isArray(question.answer) ? question.answer : [question.answer];

        return expected.some((value) => String(value).toLowerCase() === answer)
            ? total + (question.points ?? 0)
            : total;
    }, 0);
});

const totalPoints = computed(() =>
    (props.material.questions ?? []).reduce((total, question) => total + (question.points ?? 0), 0)
);

const percentage = computed(() =>
    totalPoints.value ? (score.value / totalPoints.value) * 100 : 0
);

function submitQuiz() {
    submitted.value = true;
}
</script>

<template>
    <Head :title="`${material.title} - ${course.title}`" />

    <AuthenticatedLayout>
        <div class="px-4 pt-6 pb-12 sm:px-6 lg:px-8">
            <div class="max-w-[76rem] mx-auto space-y-6">
                <header class="flex items-center gap-3 px-4 border-b h-14 shrink-0 border-border">
                    <Link
                        :href="
                            route('courses.learning-path', {
                                course: course.id,
                                class: classNumber,
                            })
                        "
                        class="inline-flex items-center justify-center rounded-md w-9 h-9 text-muted-foreground hover:bg-muted hover:text-foreground"
                        aria-label="Kembali ke jalur belajar"
                    >
                        <PhArrowLeft :size="18" aria-hidden="true" />
                    </Link>
                    <div class="min-w-0">
                        <p class="text-sm truncate text-muted-foreground">{{ course.title }}</p>
                        <h2 class="font-bold truncate text-foreground">{{ material.title }}</h2>
                    </div>
                </header>

                <div class="relative h-[600px] overflow-hidden">
                    <SidebarProvider v-model:open="sidebarOpen" class="w-full min-h-0">
                        <Sidebar
                            variant="sidebar"
                            class="absolute inset-y-0 left-0 h-[600px] w-64 min-w-64 overflow-hidden rounded-xl border border-border bg-card"
                        >
                            <SidebarHeader class="border-b border-border">
                                <SidebarMenuButton
                                    size="lg"
                                    class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                                >
                                    <div
                                        class="flex items-center justify-center rounded-lg aspect-square size-8 bg-sidebar-primary text-sidebar-primary-foreground"
                                    >
                                        <PhBookOpen
                                            class="text-primary"
                                            :size="20"
                                            aria-hidden="true"
                                        />
                                    </div>
                                    <div
                                        class="grid flex-1 text-sm leading-tight text-left group-data-[collapsible=icon]:hidden"
                                    >
                                        <p class="min-w-0 text-xs truncate text-muted-foreground">
                                            {{ course.title }}
                                        </p>
                                        <p class="min-w-0 font-semibold truncate">
                                            Chapter {{ classNumber }}
                                        </p>
                                    </div>
                                </SidebarMenuButton>
                            </SidebarHeader>

                            <SidebarContent>
                                <SidebarGroup>
                                    <SidebarGroupContent>
                                        <SidebarMenu>
                                            <Collapsible
                                                v-for="courseClass in courseClasses"
                                                :key="courseClass.id"
                                                as-child
                                                :default-open="
                                                    Number(classNumber) === courseClass.number
                                                "
                                                class="group/collapsible"
                                            >
                                                <SidebarMenuItem>
                                                    <CollapsibleTrigger as-child>
                                                        <SidebarMenuButton
                                                            :is-active="
                                                                Number(classNumber) ===
                                                                courseClass.number
                                                            "
                                                            :aria-label="`Buka materi Chapter ${courseClass.number}`"
                                                            :tooltip="`Chapter ${courseClass.number}`"
                                                        >
                                                            <span
                                                                class="min-w-0 truncate group-data-[collapsible=icon]:hidden"
                                                                >Chapter
                                                                {{ courseClass.number }}</span
                                                            >
                                                            <PhCaretRight
                                                                class="ml-auto size-4 transition-transform group-data-[collapsible=icon]:hidden group-data-[state=open]/collapsible:rotate-90"
                                                                aria-hidden="true"
                                                            />
                                                        </SidebarMenuButton>
                                                    </CollapsibleTrigger>

                                                    <CollapsibleContent>
                                                        <SidebarMenuSub>
                                                            <SidebarMenuSubItem
                                                                v-for="unit in classUnits(
                                                                    courseClass
                                                                )"
                                                                :key="unit.id"
                                                            >
                                                                <SidebarMenuSubButton
                                                                    v-if="
                                                                        isClassUnlocked(courseClass)
                                                                    "
                                                                    :as-child="true"
                                                                    :is-active="
                                                                        unit.id === material.id
                                                                    "
                                                                >
                                                                    <Link
                                                                        :href="
                                                                            unitHref(
                                                                                courseClass,
                                                                                unit
                                                                            )
                                                                        "
                                                                    >
                                                                        <component
                                                                            :is="
                                                                                unitIcon(unit.type)
                                                                            "
                                                                            class="size-4"
                                                                            aria-hidden="true"
                                                                        />
                                                                        <span
                                                                            class="min-w-0 truncate group-data-[collapsible=icon]:hidden"
                                                                            >{{ unit.title }}</span
                                                                        >
                                                                    </Link>
                                                                </SidebarMenuSubButton>
                                                                <SidebarMenuSubButton
                                                                    v-else
                                                                    disabled
                                                                    :aria-label="`${unit.title} terkunci`"
                                                                >
                                                                    <PhLock
                                                                        class="size-4"
                                                                        aria-hidden="true"
                                                                    />
                                                                    <span
                                                                        class="min-w-0 truncate group-data-[collapsible=icon]:hidden"
                                                                        >{{ unit.title }}</span
                                                                    >
                                                                </SidebarMenuSubButton>
                                                            </SidebarMenuSubItem>
                                                            <SidebarMenuSubItem
                                                                v-if="classFinalTest(courseClass)"
                                                            >
                                                                <SidebarMenuSubButton
                                                                    disabled
                                                                    :aria-label="`${classFinalTest(courseClass).title} terkunci`"
                                                                >
                                                                    <PhGraduationCap
                                                                        class="size-4"
                                                                        aria-hidden="true"
                                                                    />
                                                                    <span
                                                                        class="min-w-0 truncate group-data-[collapsible=icon]:hidden"
                                                                    >
                                                                        {{
                                                                            classFinalTest(
                                                                                courseClass
                                                                            ).title
                                                                        }}
                                                                    </span>
                                                                    <PhLock
                                                                        class="ml-auto size-4"
                                                                        aria-hidden="true"
                                                                    />
                                                                </SidebarMenuSubButton>
                                                            </SidebarMenuSubItem>
                                                        </SidebarMenuSub>
                                                    </CollapsibleContent>
                                                </SidebarMenuItem>
                                            </Collapsible>
                                        </SidebarMenu>
                                    </SidebarGroupContent>
                                </SidebarGroup>
                            </SidebarContent>
                        </Sidebar>
                        <main class="flex-1 w-full min-w-0 h-full md:!ml-64">
                            <main class="h-full px-4">
                                <Card
                                    v-if="material.type === 'text'"
                                    class="w-full h-full overflow-hidden shadow-md ring-transparent"
                                >
                                    <CardHeader
                                        class="relative pr-32 overflow-hidden bg-white min-h-36 sm:pr-44"
                                    >
                                        <CardTitle class="text-2xl">{{ material.title }}</CardTitle>
                                        <CardDescription>
                                            Baca materi berikut dengan saksama.
                                        </CardDescription>
                                        <img
                                            src="/images/brand/maskot.png"
                                            alt="Maskot Lentera"
                                            class="absolute bottom-0 w-24 h-auto right-4 sm:right-10 sm:w-32"
                                        />
                                    </CardHeader>
                                    <CardContent>
                                        <p class="text-base leading-8 text-foreground">
                                            {{ material.content }}
                                        </p>
                                    </CardContent>
                                    <CardFooter>
                                        <Button :as-child="true" class="text-white">
                                            <Link
                                                :href="
                                                    route('courses.learning-path', {
                                                        course: course.id,
                                                        class: classNumber,
                                                    })
                                                "
                                            >
                                                Kembali ke jalur belajar
                                                <PhArrowRight :size="16" aria-hidden="true" />
                                            </Link>
                                        </Button>
                                    </CardFooter>
                                </Card>

                                <template v-else-if="material.type === 'video'">
                                    <Card class="min-h-full !overflow-visible">
                                        <CardHeader>
                                            <CardTitle class="text-2xl">{{
                                                material.title
                                            }}</CardTitle>
                                            <CardDescription
                                                >Video pembelajaran ·
                                                {{ material.duration }}</CardDescription
                                            >
                                        </CardHeader>
                                        <CardContent>
                                            <iframe
                                                v-if="youtubeEmbedUrl"
                                                :src="youtubeEmbedUrl"
                                                :title="material.title"
                                                class="w-full rounded-lg aspect-video"
                                                allow="
                                                    accelerometer;
                                                    autoplay;
                                                    clipboard-write;
                                                    encrypted-media;
                                                    gyroscope;
                                                    picture-in-picture;
                                                    web-share;
                                                "
                                                allowfullscreen
                                            ></iframe>
                                            <video
                                                v-else
                                                controls
                                                class="w-full rounded-lg aspect-video bg-foreground"
                                                :src="material.url"
                                            >
                                                Browser kamu tidak mendukung pemutar video.
                                            </video>
                                        </CardContent>
                                    </Card>
                                    <VideoSummary
                                        v-if="material.summary"
                                        :summary="material.summary"
                                        :title="material.summaryTitle ?? 'Rangkuman Video'"
                                    />
                                </template>

                                <Card v-else-if="material.type === 'quiz'" class="h-full">
                                    <CardHeader>
                                        <CardTitle class="text-2xl">{{ material.title }}</CardTitle>
                                        <CardDescription>
                                            Jawab semua pertanyaan. Nilai lulus minimal
                                            {{ material.passingScore }}%.
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent class="space-y-8">
                                        <fieldset
                                            v-for="(question, questionIndex) in material.questions"
                                            :key="question.id"
                                            class="space-y-4"
                                        >
                                            <legend class="font-semibold text-foreground">
                                                {{ questionIndex + 1 }}. {{ question.question }}
                                            </legend>

                                            <div
                                                v-if="question.type === 'multiple_choice'"
                                                class="space-y-3"
                                            >
                                                <label
                                                    v-for="option in question.options"
                                                    :key="option.id"
                                                    class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer border-border hover:bg-muted"
                                                >
                                                    <input
                                                        v-model="answers[question.id]"
                                                        type="radio"
                                                        :name="question.id"
                                                        :value="option.id"
                                                        class="text-primary focus:ring-primary"
                                                    />
                                                    <span>{{ option.text }}</span>
                                                </label>
                                            </div>
                                            <Input
                                                v-else
                                                v-model="answers[question.id]"
                                                placeholder="Tulis jawabanmu"
                                                :aria-label="`Jawaban untuk ${question.question}`"
                                            />

                                            <p
                                                v-if="submitted"
                                                class="text-sm"
                                                :class="{
                                                    'text-green-600': (Array.isArray(
                                                        question.answer
                                                    )
                                                        ? question.answer
                                                        : [question.answer]
                                                    ).some(
                                                        (value) =>
                                                            String(value).toLowerCase() ===
                                                            answers[question.id]
                                                                ?.trim()
                                                                .toLowerCase()
                                                    ),
                                                    'text-destructive': !(
                                                        Array.isArray(question.answer)
                                                            ? question.answer
                                                            : [question.answer]
                                                    ).some(
                                                        (value) =>
                                                            String(value).toLowerCase() ===
                                                            answers[question.id]
                                                                ?.trim()
                                                                .toLowerCase()
                                                    ),
                                                }"
                                            >
                                                {{ question.explanation }}
                                            </p>
                                        </fieldset>
                                    </CardContent>
                                    <CardFooter
                                        class="flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <div
                                            v-if="submitted"
                                            class="flex items-center gap-2 text-sm font-semibold"
                                        >
                                            <PhCheckCircle
                                                :size="20"
                                                class="text-primary"
                                                aria-hidden="true"
                                            />
                                            Nilai {{ score }}/{{ totalPoints }} ({{ percentage }}%)
                                        </div>
                                        <Button type="button" @click="submitQuiz">
                                            {{ submitted ? 'Periksa lagi' : 'Periksa jawaban' }}
                                        </Button>
                                    </CardFooter>
                                </Card>
                            </main>
                        </main>
                    </SidebarProvider>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
