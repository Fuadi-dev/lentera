<script setup>
import Card from '@/Components/ui/card/Card.vue';
import CardContent from '@/Components/ui/card/CardContent.vue';
import CardDescription from '@/Components/ui/card/CardDescription.vue';
import CardHeader from '@/Components/ui/card/CardHeader.vue';
import CardTitle from '@/Components/ui/card/CardTitle.vue';
import VideoSummary from '@/Components/Course/VideoSummary.vue';

const props = defineProps({
    material: { type: Object, required: true },
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
    <Card class="min-h-full !overflow-visible">
        <CardHeader>
            <CardTitle class="text-2xl">{{ material.title }}</CardTitle>
            <CardDescription>Video pembelajaran · {{ material.duration }}</CardDescription>
        </CardHeader>
        <CardContent>
            <iframe
                v-if="youtubeEmbedUrl()"
                :src="youtubeEmbedUrl()"
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
    </Card>
</template>
