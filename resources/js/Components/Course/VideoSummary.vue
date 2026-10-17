<script setup>
import { onBeforeUnmount } from 'vue';
import { Headphones } from 'lucide-vue-next';
import Button from '@/Components/ui/button/Button.vue';

const props = defineProps({
    summary: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: 'Rangkuman Video',
    },
    source: {
        type: String,
        default: null,
    },
});

function speakText() {
    if (!('speechSynthesis' in window)) {
        alert('Fitur suara belum tersedia di browser ini.');
        return;
    }

    window.speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(props.summary);
    utterance.lang = 'id-ID';
    window.speechSynthesis.speak(utterance);
}

onBeforeUnmount(() => {
    window.speechSynthesis?.cancel();
});
</script>

<template>
    <section class="px-6 pb-6" aria-labelledby="video-summary-heading">
        <div class="flex items-center justify-between gap-3">
            <h3 id="video-summary-heading" class="text-xl font-semibold text-foreground">
                {{ title }}
            </h3>
            <Button
                type="button"
                variant="outline"
                class="gap-2 rounded-xl font-semibold border-slate-200 text-slate-700 hover:text-slate-900 hover:bg-slate-50 cursor-pointer"
                @click="speakText"
            >
                <Headphones :size="17" class="text-primary shrink-0" aria-hidden="true" />
                <span>Dengarkan Rangkuman</span>
            </Button>
        </div>
        <p class="mt-3 text-base leading-8 text-justify whitespace-pre-line text-muted-foreground">
            {{ summary }}
        </p>
        <p v-if="source" class="mt-4 text-sm text-muted-foreground">
            Sumber:
            <a
                :href="source"
                target="_blank"
                rel="noopener noreferrer"
                class="font-medium text-primary hover:underline"
            >
                YouTube
            </a>
        </p>
    </section>
</template>
