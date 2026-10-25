<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import axios from 'axios';
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/Components/ui/sheet';
import {
    PhChatTeardropDots,
    PhLightbulb,
    PhPaperPlaneRight,
    PhShieldWarning,
    PhSparkle,
    PhSpeakerHigh,
    PhSpeakerSimpleSlash,
    PhX,
} from '@phosphor-icons/vue';

const props = defineProps({
    unit: {
        type: Object,
        required: true,
    },
});

const isOpen = ref(false);
const sessionId = ref(null);
const messages = ref([]);
const remainingSession = ref(10);
const remainingToday = ref(null);
const inputMessage = ref('');
const isLoadingSession = ref(false);
const isSending = ref(false);
const messagesContainer = ref(null);
const activeSpeakingIndex = ref(null);

const MAX_CHARACTERS = 200;

const quickReplies = [
    'Jelaskan lebih mudah',
    'Beri contoh',
    'Jelaskan konsepnya',
    'Ulangi dengan kata lain',
];

const isLimitReached = computed(() => {
    return remainingSession.value <= 0 || (remainingToday.value !== null && remainingToday.value <= 0);
});

const charactersRemaining = computed(() => {
    return MAX_CHARACTERS - inputMessage.value.length;
});

function scrollToBottom() {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
}

async function loadSession() {
    if (!props.unit?.id) {
        return;
    }

    isLoadingSession.value = true;
    try {
        const response = await axios.post('/ai/chat/sessions', {
            unit_id: props.unit.id,
        });

        sessionId.value = response.data.session_id;
        messages.value = response.data.messages || [];
        remainingSession.value = response.data.remaining ?? 10;
        scrollToBottom();
    } catch (error) {
        console.error('Gagal memuat sesi chat tutor:', error);
    } finally {
        isLoadingSession.value = false;
    }
}

watch(isOpen, (open) => {
    if (open) {
        if (!sessionId.value) {
            loadSession();
        } else {
            scrollToBottom();
        }
    } else {
        stopSpeech();
    }
});

watch(
    () => props.unit?.id,
    () => {
        sessionId.value = null;
        messages.value = [];
        stopSpeech();
        if (isOpen.value) {
            loadSession();
        }
    }
);

async function handleSendMessage(textToSend = null) {
    const text = (textToSend ?? inputMessage.value).trim();
    if (!text || isSending.value || isLimitReached.value) {
        return;
    }

    if (text.length > MAX_CHARACTERS) {
        return;
    }

    inputMessage.value = '';

    messages.value.push({
        id: 'user-' + Date.now(),
        role: 'user',
        content: text,
        source: 'user',
        created_at: new Date().toISOString(),
    });
    scrollToBottom();

    isSending.value = true;

    try {
        const response = await axios.post(`/ai/chat/sessions/${sessionId.value}/messages`, {
            content: text,
        });

        messages.value.push({
            id: 'ai-' + Date.now(),
            role: 'model',
            content: response.data.message,
            source: response.data.source || 'ai',
            created_at: new Date().toISOString(),
        });

        if (response.data.remaining) {
            remainingSession.value = response.data.remaining.session ?? Math.max(0, remainingSession.value - 1);
            remainingToday.value = response.data.remaining.today ?? remainingToday.value;
        } else {
            remainingSession.value = Math.max(0, remainingSession.value - 1);
        }
    } catch (error) {
        console.error('Gagal mengirim pesan chat tutor:', error);
        messages.value.push({
            id: 'err-' + Date.now(),
            role: 'model',
            content: 'Si Lentera lagi sibuk. Coba baca penjelasan di atas dulu ya.',
            source: 'fallback',
            created_at: new Date().toISOString(),
        });
    } finally {
        isSending.value = false;
        scrollToBottom();
    }
}

function handleQuickReply(replyText) {
    handleSendMessage(replyText);
}

function toggleSpeech(text, index) {
    if (!('speechSynthesis' in window)) {
        return;
    }

    if (activeSpeakingIndex.value === index) {
        stopSpeech();
        return;
    }

    stopSpeech();
    activeSpeakingIndex.value = index;

    const cleanText = text.replace(/[*#_`~]/g, '');
    const utterance = new SpeechSynthesisUtterance(cleanText);
    utterance.lang = 'id-ID';
    utterance.rate = 0.95;

    utterance.onend = () => {
        if (activeSpeakingIndex.value === index) {
            activeSpeakingIndex.value = null;
        }
    };

    utterance.onerror = () => {
        if (activeSpeakingIndex.value === index) {
            activeSpeakingIndex.value = null;
        }
    };

    window.speechSynthesis.speak(utterance);
}

function stopSpeech() {
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();
    }
    activeSpeakingIndex.value = null;
}

onBeforeUnmount(() => {
    stopSpeech();
});
</script>

<template>
    <Sheet v-model:open="isOpen">
        <!-- Floating Action Trigger -->
        <SheetTrigger as-child>
            <button
                type="button"
                class="fixed bottom-6 right-6 z-40 flex items-center gap-2.5 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 px-4 py-3 text-white shadow-xl hover:shadow-2xl hover:scale-105 active:scale-95 transition-all duration-200 cursor-pointer focus:outline-none focus:ring-4 focus:ring-orange-200"
                aria-label="Buka Tanya Si Lentera"
                title="Tanya Si Lentera"
            >
                <img
                    src="/images/brand/maskot.png"
                    alt=""
                    class="size-7 object-contain drop-shadow"
                    aria-hidden="true"
                />
                <span class="font-extrabold text-sm tracking-wide">Tanya Si Lentera</span>
                <PhSparkle :size="18" weight="fill" class="text-amber-200 animate-pulse" aria-hidden="true" />
            </button>
        </SheetTrigger>

        <SheetContent
            side="right"
            :show-close-button="false"
            class="w-[92vw] sm:max-w-md md:max-w-lg p-0 bg-stone-50 border-l border-orange-100 flex flex-col h-full overflow-hidden shadow-2xl"
        >
            <!-- Accessible Sheet Header -->
            <SheetHeader class="bg-gradient-to-r from-orange-500 via-orange-600 to-amber-600 text-white p-4 sm:p-5 flex flex-row items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <img
                        src="/images/brand/maskot.png"
                        alt="Maskot Si Lentera"
                        class="size-11 rounded-full bg-white/20 p-1 backdrop-blur ring-2 ring-white/40 shadow-sm object-contain"
                    />
                    <div>
                        <SheetTitle class="text-lg font-extrabold text-white tracking-tight flex items-center gap-1.5">
                            Tanya Si Lentera
                            <PhSparkle :size="16" weight="fill" class="text-amber-200" />
                        </SheetTitle>
                        <SheetDescription class="text-xs text-orange-100 font-medium">
                            Tutor Pendamping Belajar SD
                        </SheetDescription>
                    </div>
                </div>

                <SheetClose
                    class="rounded-full p-2 text-white/80 hover:text-white hover:bg-white/20 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-white"
                    aria-label="Tutup panel chat"
                >
                    <PhX :size="20" weight="bold" aria-hidden="true" />
                </SheetClose>
            </SheetHeader>

            <!-- Context & Remaining Limit Banner -->
            <div class="bg-orange-50/90 border-b border-orange-200/70 px-4 py-2.5 flex items-center justify-between text-xs text-orange-900 font-semibold shrink-0">
                <div class="truncate max-w-[65%] flex items-center gap-1.5">
                    <span class="text-orange-500 shrink-0">●</span>
                    <span class="truncate">{{ unit.title }}</span>
                </div>
                <div class="shrink-0 bg-white/80 px-2 py-0.5 rounded-full border border-orange-200 text-orange-800 text-[11px] font-bold">
                    Sisa: {{ remainingSession }} pertanyaan
                </div>
            </div>

            <!-- Messages Container -->
            <div
                ref="messagesContainer"
                role="log"
                aria-live="polite"
                class="flex-1 overflow-y-auto p-4 space-y-4"
            >
                <!-- Initial Welcome Greeting -->
                <div class="flex items-start gap-3">
                    <img
                        src="/images/brand/maskot.png"
                        alt="Si Lentera"
                        class="size-9 rounded-full bg-orange-100 p-1 border border-orange-200 object-contain shrink-0 mt-0.5"
                    />
                    <div class="bg-white rounded-2xl rounded-tl-sm p-3.5 shadow-xs border border-slate-200/70 text-slate-800 text-sm leading-relaxed max-w-[88%] space-y-2">
                        <div class="flex items-center gap-1 text-[11px] font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-md border border-orange-200/50 w-fit">
                            <PhSparkle :size="12" weight="fill" />
                            <span>Tutor Belajar Lentera</span>
                        </div>
                        <p>
                            Halo teman! 👋 Aku <strong>Si Lentera</strong>. Kalau ada materi di halaman ini yang belum kamu pahami, tanyakan saja ya! Aku siap membantu menjelaskan dengan cara yang mudah.
                        </p>
                    </div>
                </div>

                <!-- Chat History Messages -->
                <div
                    v-for="(msg, idx) in messages"
                    :key="msg.id || idx"
                    class="flex flex-col"
                    :class="msg.role === 'user' ? 'items-end' : 'items-start'"
                >
                    <!-- User Message -->
                    <div
                        v-if="msg.role === 'user'"
                        class="bg-orange-500 text-white rounded-2xl rounded-tr-sm p-3.5 max-w-[85%] shadow-xs text-sm leading-relaxed"
                    >
                        <p class="whitespace-pre-line">{{ msg.content }}</p>
                    </div>

                    <!-- AI / Model Message -->
                    <div
                        v-else
                        class="flex items-start gap-3 max-w-[90%]"
                    >
                        <img
                            src="/images/brand/maskot.png"
                            alt="Si Lentera"
                            class="size-8 rounded-full bg-orange-100 p-0.5 border border-orange-200 object-contain shrink-0 mt-0.5"
                        />
                        <div
                            :class="[
                                'rounded-2xl rounded-tl-sm p-3.5 shadow-xs text-sm leading-relaxed space-y-2 w-full',
                                msg.source === 'blocked_pii'
                                    ? 'bg-amber-50 border border-amber-200 text-amber-900'
                                    : msg.source === 'fallback'
                                    ? 'bg-rose-50 border border-rose-200 text-slate-800'
                                    : 'bg-white border border-slate-200/70 text-slate-800'
                            ]"
                        >
                            <!-- Label Sumber -->
                            <div class="flex items-center justify-between gap-2">
                                <span
                                    v-if="msg.source === 'blocked_pii'"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-100/80 px-2 py-0.5 rounded-md"
                                >
                                    <PhShieldWarning :size="12" weight="bold" />
                                    Privasi Terjaga
                                </span>
                                <span
                                    v-else-if="msg.source === 'fallback'"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 bg-rose-100/80 px-2 py-0.5 rounded-md"
                                >
                                    <PhLightbulb :size="12" weight="bold" />
                                    Pesan Sistem
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-md border border-orange-200/50"
                                >
                                    <PhSparkle :size="12" weight="fill" />
                                    Dijelaskan oleh AI
                                </span>

                                <!-- Dengarkan Button -->
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-400"
                                    :class="activeSpeakingIndex === idx ? 'bg-orange-100 text-orange-700 ring-1 ring-orange-300' : 'text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200'"
                                    :aria-label="activeSpeakingIndex === idx ? 'Hentikan suara' : 'Dengarkan penjelasan'"
                                    title="Dengarkan penjelasan suara"
                                    @click="toggleSpeech(msg.content, idx)"
                                >
                                    <component
                                        :is="activeSpeakingIndex === idx ? PhSpeakerSimpleSlash : PhSpeakerHigh"
                                        :size="15"
                                        weight="bold"
                                        aria-hidden="true"
                                    />
                                    <span>{{ activeSpeakingIndex === idx ? 'Berhenti' : 'Dengarkan' }}</span>
                                </button>
                            </div>

                            <p class="whitespace-pre-line text-sm">{{ msg.content }}</p>
                        </div>
                    </div>
                </div>

                <!-- Loading Indicator State -->
                <div
                    v-if="isSending"
                    class="flex items-start gap-3 max-w-[88%]"
                >
                    <img
                        src="/images/brand/maskot.png"
                        alt="Si Lentera"
                        class="size-8 rounded-full bg-orange-100 p-0.5 border border-orange-200 object-contain shrink-0 mt-0.5 animate-pulse"
                    />
                    <div class="bg-white rounded-2xl rounded-tl-sm p-3.5 shadow-xs border border-slate-200/70 text-slate-700 text-xs font-semibold flex items-center gap-2">
                        <span>Si Lentera sedang menyiapkan jawaban...</span>
                        <span class="flex gap-1">
                            <span class="size-1.5 bg-orange-500 rounded-full animate-bounce [animation-delay:-0.3s]"></span>
                            <span class="size-1.5 bg-orange-500 rounded-full animate-bounce [animation-delay:-0.15s]"></span>
                            <span class="size-1.5 bg-orange-500 rounded-full animate-bounce"></span>
                        </span>
                    </div>
                </div>

                <!-- Limit Exceeded Info Card -->
                <div
                    v-if="isLimitReached"
                    class="rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-center text-xs text-amber-900 font-medium"
                >
                    <p class="font-bold text-sm mb-1">Hari ini Si Lentera istirahat dulu ya!</p>
                    <p>Batas pertanyaan harian atau sesi telah tercapai. Lanjut belajar besok ya!</p>
                </div>
            </div>

            <!-- Footer Area: Quick Replies & Input -->
            <div class="p-3 sm:p-4 bg-white border-t border-slate-200/80 space-y-2.5 shrink-0">
                <!-- Quick Reply Buttons -->
                <div
                    v-if="!isLimitReached"
                    class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none"
                    aria-label="Tombol pertanyaan cepat"
                >
                    <button
                        v-for="reply in quickReplies"
                        :key="reply"
                        type="button"
                        class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-full bg-orange-50/80 border border-orange-200 text-orange-800 hover:bg-orange-100 hover:border-orange-300 transition-colors shrink-0 shadow-2xs cursor-pointer active:scale-95 disabled:opacity-50"
                        :disabled="isSending"
                        @click="handleQuickReply(reply)"
                    >
                        <PhSparkle :size="12" weight="fill" class="text-orange-500" aria-hidden="true" />
                        <span>{{ reply }}</span>
                    </button>
                </div>

                <!-- Input Form -->
                <form
                    class="relative"
                    @submit.prevent="handleSendMessage()"
                >
                    <label
                        for="ai-chat-input"
                        class="sr-only"
                    >
                        Tulis pertanyaan tentang materi ini
                    </label>

                    <div class="relative">
                        <input
                            id="ai-chat-input"
                            v-model="inputMessage"
                            type="text"
                            :maxlength="MAX_CHARACTERS"
                            :disabled="isSending || isLimitReached"
                            placeholder="Tanya materi ini (maks 200 huruf)..."
                            class="w-full text-sm rounded-xl border border-slate-300 bg-slate-50/50 px-3.5 py-3 pr-12 focus:border-orange-500 focus:bg-white focus:ring-3 focus:ring-orange-100 outline-none transition disabled:bg-slate-100 disabled:text-slate-400 placeholder:text-slate-400"
                        />

                        <button
                            type="submit"
                            :disabled="!inputMessage.trim() || isSending || isLimitReached"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-2 rounded-lg bg-orange-500 text-white hover:bg-orange-600 disabled:bg-slate-200 disabled:text-slate-400 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-400"
                            aria-label="Kirim pertanyaan"
                        >
                            <PhPaperPlaneRight :size="16" weight="fill" aria-hidden="true" />
                        </button>
                    </div>

                    <!-- Character counter -->
                    <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400 px-1">
                        <span>Pendamping belajar ramah anak</span>
                        <span :class="charactersRemaining < 20 ? 'text-rose-500 font-bold' : ''">
                            {{ inputMessage.length }} / {{ MAX_CHARACTERS }} karakter
                        </span>
                    </div>
                </form>
            </div>
        </SheetContent>
    </Sheet>
</template>
