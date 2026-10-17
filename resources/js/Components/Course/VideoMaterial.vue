<script setup>
import Card from '@/Components/ui/card/Card.vue';
import CardContent from '@/Components/ui/card/CardContent.vue';
import CardDescription from '@/Components/ui/card/CardDescription.vue';
import CardHeader from '@/Components/ui/card/CardHeader.vue';
import CardTitle from '@/Components/ui/card/CardTitle.vue';
import CardFooter from '@/Components/ui/card/CardFooter.vue';
import VideoSummary from '@/Components/Course/VideoSummary.vue';
import NextMaterialButton from '@/Components/Course/NextMaterialButton.vue';
import MarkUnitDoneButton from '@/Components/Course/MarkUnitDoneButton.vue';

const props = defineProps({
    material: { type: Object, required: true },
    nextUnitHref: { type: String, default: null },
    nextUnitTitle: { type: String, default: '' },
    completed: { type: Boolean, default: false },
});

function youtubeEmbedUrl() {
    if (!props.material.media_url) {
        return null;
    }

    try {
        const url = new URL(props.material.media_url);
        let videoId =
            url.hostname === 'youtu.be' ? url.pathname.slice(1) : url.searchParams.get('v');

        if (url.hostname.includes('youtube.com') && url.pathname.startsWith('/embed/')) {
            videoId = url.pathname.split('/')[2];
        }

        return videoId ? `https://www.youtube.com/embed/${videoId}` : null;
    } catch {
        return null;
    }
}
</script>

<template>
    <Card class="w-full overflow-hidden shadow-sm bg-white rounded-3xl border border-slate-100">
        <CardHeader class="p-6 sm:p-8 bg-white border-b border-slate-100">
            <CardTitle class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ material.title }}</CardTitle>
            <CardDescription class="text-sm text-slate-500 font-medium">Video pembelajaran · {{ material.duration }}</CardDescription>
        </CardHeader>
        <CardContent class="p-6 sm:p-8">
            <iframe
                v-if="youtubeEmbedUrl()"
                :src="youtubeEmbedUrl()"
                :title="material.title"
                class="w-full rounded-2xl aspect-video shadow-sm"
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
                class="w-full rounded-2xl aspect-video bg-foreground shadow-sm"
                :src="material.media_url"
            >
                Browser kamu tidak mendukung pemutar video.
            </video>
        </CardContent>
        <VideoSummary
            v-if="material.summary"
            :summary="material.summary"
            :title="material.summaryTitle ?? 'Rangkuman Video'"
            :source="material.source"
        />
        <CardFooter class="p-6 sm:p-8 pt-6 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <MarkUnitDoneButton :unit-id="material.id" :completed="completed" />
            <NextMaterialButton v-if="nextUnitHref" :href="nextUnitHref" :title="nextUnitTitle" />
        </CardFooter>
    </Card>
</template>
