<script setup>
import { Link } from '@inertiajs/vue3';
import { PhArrowRight } from '@phosphor-icons/vue';
import Button from '@/Components/ui/button/Button.vue';
import Card from '@/Components/ui/card/Card.vue';
import CardContent from '@/Components/ui/card/CardContent.vue';
import CardDescription from '@/Components/ui/card/CardDescription.vue';
import CardFooter from '@/Components/ui/card/CardFooter.vue';
import CardHeader from '@/Components/ui/card/CardHeader.vue';
import CardTitle from '@/Components/ui/card/CardTitle.vue';

defineProps({
    course: { type: Object, required: true },
    classNumber: { type: [Number, String], required: true },
    material: { type: Object, required: true },
});
</script>

<template>
    <Card class="w-full h-full overflow-hidden shadow-md ring-transparent">
        <CardHeader class="relative pr-32 overflow-hidden bg-white min-h-36 sm:pr-44">
            <CardTitle class="text-2xl">{{ material.title }}</CardTitle>
            <CardDescription>Baca materi berikut dengan saksama.</CardDescription>
            <img
                src="/images/brand/maskot.png"
                alt="Maskot Lentera"
                class="absolute bottom-0 w-24 h-auto right-4 sm:right-10 sm:w-32"
            />
        </CardHeader>
        <CardContent>
            <p class="text-base leading-8 text-foreground">{{ material.content }}</p>
        </CardContent>
        <CardFooter>
            <Button :as-child="true" class="text-white">
                <Link
                    :href="
                        route('courses.learning-path', { course: course.id, class: classNumber })
                    "
                >
                    Kembali ke jalur belajar
                    <PhArrowRight :size="16" aria-hidden="true" />
                </Link>
            </Button>
        </CardFooter>
    </Card>
</template>
