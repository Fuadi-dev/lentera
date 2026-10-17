<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/Components/ui/dialog';
import Button from '@/Components/ui/button/Button.vue';
import { PhWind, PhFire, PhArrowClockwise, PhTrophy } from '@phosphor-icons/vue';

const page = usePage();
const isOpen = ref(false);
const isExtinguished = ref(false);
const isCracking = ref(false);

const previousStreak = ref(3);
const longestStreak = ref(5);
const currentDisplayCount = ref(3);

let animationTimeout1 = null;
let animationTimeout2 = null;

// Play a subtle gentle extinguish sound effect using Web Audio API
function playExtinguishSound() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        if (ctx.state === 'suspended') {
            ctx.resume();
        }

        // Noise buffer for gentle smoke puff
        const bufferSize = ctx.sampleRate * 0.4;
        const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
        const data = buffer.getChannelData(0);
        for (let i = 0; i < bufferSize; i++) {
            data[i] = (Math.random() * 2 - 1) * Math.exp(-i / (ctx.sampleRate * 0.12));
        }

        const noise = ctx.createBufferSource();
        noise.buffer = buffer;

        const filter = ctx.createBiquadFilter();
        filter.type = 'lowpass';
        filter.frequency.setValueAtTime(800, ctx.currentTime);
        filter.frequency.exponentialRampToValueAtTime(120, ctx.currentTime + 0.35);

        const gain = ctx.createGain();
        gain.gain.setValueAtTime(0.2, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);

        noise.connect(filter);
        filter.connect(gain);
        gain.connect(ctx.destination);

        noise.start();
    } catch {
        // Silently skip if audio is restricted
    }
}

function startExtinguishAnimation() {
    clearTimeouts();
    isCracking.value = false;
    isExtinguished.value = false;
    currentDisplayCount.value = previousStreak.value;

    // Stage 1: Flame begins trembling and crack starts appearing (0.65s)
    animationTimeout1 = setTimeout(() => {
        isCracking.value = true;
    }, 650);

    // Stage 2: Crack snaps, flame turns to smoke & gray icon, number drops to 0 (1.15s)
    animationTimeout2 = setTimeout(() => {
        isExtinguished.value = true;
        currentDisplayCount.value = 0;
        playExtinguishSound();
    }, 1150);
}

function clearTimeouts() {
    if (animationTimeout1) clearTimeout(animationTimeout1);
    if (animationTimeout2) clearTimeout(animationTimeout2);
}

function openModal(data = {}) {
    previousStreak.value = Number(data.previous_streak ?? 3);
    longestStreak.value = Number(data.longest_streak ?? Math.max(previousStreak.value, 5));
    isOpen.value = true;
    startExtinguishAnimation();
}

function closeModal() {
    isOpen.value = false;
    clearTimeouts();
}

function handleStartLearning() {
    closeModal();
    // Smooth scroll to learning section if on dashboard
    const coursesSection = document.getElementById('courses-heading') || document.querySelector('section');
    if (coursesSection) {
        coursesSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        router.visit(route('dashboard'));
    }
}

// Auto-detection from Backend Inertia auth props
watch(
    () => page.props.auth?.streak_broken,
    (brokenInfo) => {
        if (brokenInfo && brokenInfo.is_broken) {
            const todayKey = new Date().toISOString().split('T')[0];
            const storageKey = `lentera_streak_broken_seen_${page.props.auth?.user?.id}_${todayKey}`;
            if (!localStorage.getItem(storageKey)) {
                openModal({
                    previous_streak: brokenInfo.previous_streak,
                    longest_streak: brokenInfo.longest_streak,
                });
                localStorage.setItem(storageKey, 'true');
            }
        }
    },
    { immediate: true, deep: true }
);

function handleCustomOpenEvent(event) {
    const detail = event.detail || {};
    openModal({
        previous_streak: detail.previous_streak ?? (page.props.auth?.streak_broken?.previous_streak || 3),
        longest_streak: detail.longest_streak ?? (page.props.auth?.streak_broken?.longest_streak || 5),
    });
}

onMounted(() => {
    window.addEventListener('open-streak-broken', handleCustomOpenEvent);

    // Check for demo trigger query: ?demo_streak_broken=1
    if (typeof window !== 'undefined') {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('demo_streak_broken') === '1' || urlParams.get('streak_broken') === '1') {
            setTimeout(() => {
                openModal({ previous_streak: 5, longest_streak: 7 });
            }, 500);
        }
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('open-streak-broken', handleCustomOpenEvent);
    clearTimeouts();
});
</script>

<template>
    <Dialog :open="isOpen" @update:open="(val) => (isOpen = val)">
        <DialogContent class="max-w-sm sm:max-w-md p-6 overflow-hidden text-center border-slate-200 bg-gradient-to-b from-slate-50 via-white to-blue-50/30 shadow-2xl rounded-3xl">
            <div class="relative py-3 flex flex-col items-center">
                <!-- Glowing Cold / Frosty Backdrop Halo -->
                <div class="relative flex items-center justify-center w-32 h-32 my-1">
                    <div
                        class="absolute inset-0 rounded-full transition-all duration-700 blur-2xl"
                        :class="isExtinguished ? 'bg-slate-300/40' : 'bg-orange-400/25 animate-pulse'"
                        aria-hidden="true"
                    ></div>
                    <div
                        class="absolute inset-3 rounded-full transition-all duration-700 blur-lg"
                        :class="isExtinguished ? 'bg-blue-100/40' : 'bg-amber-300/30'"
                        aria-hidden="true"
                    ></div>

                    <!-- Rising Smoke Particles when extinguished -->
                    <div v-if="isExtinguished" class="absolute inset-0 pointer-events-none" aria-hidden="true">
                        <span class="smoke-puff smoke-puff-1">💨</span>
                        <span class="smoke-puff smoke-puff-2">🌫️</span>
                        <span class="smoke-puff smoke-puff-3">💨</span>
                    </div>

                    <!-- Flame Image Container with Crack and Extinguish Effects -->
                    <div class="relative w-24 h-24 flex items-center justify-center">
                        <img
                            :src="isExtinguished ? '/images/ui/not streak.png' : '/images/ui/streak.png'"
                            alt="Streak Terputus"
                            class="relative w-24 h-24 object-contain transition-all duration-500"
                            :class="[
                                isCracking && !isExtinguished ? 'streak-crack-shiver' : '',
                                isExtinguished ? 'streak-extinguished-pop' : '',
                            ]"
                        />

                        <!-- SVG Crack line overlaid while cracking -->
                        <svg
                            v-if="isCracking && !isExtinguished"
                            class="absolute inset-0 w-full h-full pointer-events-none crack-svg-line"
                            viewBox="0 0 100 100"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M 46 15 L 53 38 L 47 55 L 55 72 L 50 88"
                                stroke="#ffffff"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="animate-pulse"
                            />
                            <path
                                d="M 46 15 L 53 38 L 47 55 L 55 72 L 50 88"
                                stroke="#1e293b"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                </div>

                <!-- Replay Animation Button -->
                <button
                    type="button"
                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-400 hover:text-slate-600 transition-colors mt-1 mb-2 px-2.5 py-0.5 rounded-full hover:bg-slate-100"
                    @click="startExtinguishAnimation"
                    title="Putar ulang animasi streak padam"
                >
                    <PhArrowClockwise :size="13" class="animate-spin-hover" />
                    <span>Putar Ulang Efek</span>
                </button>

                <DialogHeader class="items-center text-center space-y-2.5">
                    <!-- Status Tag -->
                    <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold tracking-wide uppercase transition-all duration-300"
                        :class="isExtinguished ? 'bg-slate-100 border border-slate-200 text-slate-700' : 'bg-orange-100 border border-orange-200 text-orange-700'"
                    >
                        <PhWind v-if="isExtinguished" :size="15" weight="bold" class="text-slate-500 animate-pulse" />
                        <PhFire v-else :size="15" weight="fill" class="text-orange-500" />
                        <span>{{ isExtinguished ? 'Streak Terputus' : 'Memeriksa Api...' }}</span>
                    </div>

                    <!-- Dynamic Title & Falling Streak Number -->
                    <div class="space-y-1">
                        <DialogTitle class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">
                            Yah, Api Belajarmu Padam!
                        </DialogTitle>
                        
                        <!-- Animated Drop from Previous Streak to 0 -->
                        <div class="flex items-center justify-center gap-2 pt-1">
                            <span
                                v-if="!isExtinguished"
                                class="text-xl font-black text-orange-600 transition-all duration-300 inline-block scale-110"
                            >
                                🔥 {{ currentDisplayCount }} Hari
                            </span>
                            <span
                                v-else
                                class="text-xl font-black text-slate-500 transition-all duration-500 inline-block number-drop-animate"
                            >
                                💨 0 Hari
                            </span>
                            <span
                                v-if="isExtinguished && previousStreak > 0"
                                class="text-xs font-semibold text-slate-400 line-through"
                            >
                                (kemarin: {{ previousStreak }} hari)
                            </span>
                        </div>
                    </div>

                    <DialogDescription class="text-sm text-gray-600 font-medium max-w-xs mx-auto leading-relaxed pt-1">
                        Kamu tidak sempat belajar kemarin, jadi apimu harus dimulai kembali dari awal. Tapi jangan khawatir, juara sejati selalu bangkit lagi! 💪
                    </DialogDescription>
                </DialogHeader>

                <!-- Statistics Box -->
                <div class="w-full mt-4 p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-around text-xs">
                    <div class="text-center">
                        <span class="block text-slate-400 font-medium">Streak Terakhir</span>
                        <strong class="text-slate-700 text-sm font-bold">{{ previousStreak }} Hari</strong>
                    </div>
                    <div class="w-px h-7 bg-slate-200" aria-hidden="true"></div>
                    <div class="text-center">
                        <span class="block text-slate-400 font-medium flex items-center justify-center gap-1">
                            <PhTrophy :size="12" weight="fill" class="text-amber-500" />
                            Rekor Terbaik
                        </span>
                        <strong class="text-amber-600 text-sm font-bold">{{ longestStreak }} Hari</strong>
                    </div>
                </div>

                <!-- Motivation Encouragement Note -->
                <div class="mt-3.5 px-3.5 py-2 rounded-xl bg-orange-50/80 border border-orange-100 text-xs text-orange-800 font-medium leading-relaxed">
                    🌟 <strong>Tips:</strong> Cukup selesaikan 1 materi atau latihan hari ini untuk langsung <strong>menyalakan kembali api belajarmu!</strong>
                </div>

                <!-- Action Button -->
                <div class="w-full mt-5">
                    <Button
                        type="button"
                        class="w-full py-6 text-base font-bold text-white shadow-lg shadow-orange-500/25 bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 hover:from-orange-600 hover:to-amber-600 rounded-2xl transition-all duration-200 hover:shadow-orange-500/40 transform active:scale-[0.98]"
                        @click="handleStartLearning"
                    >
                        Ayo Nyalakan Lagi Sekarang! 🔥
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
/* Crack & Shiver Keyframes */
@keyframes streak-crack-shiver {
    0% { transform: scale(1) rotate(0deg); }
    15% { transform: scale(1.08) rotate(-7deg); }
    30% { transform: scale(0.96) rotate(6deg); }
    45% { transform: scale(1.05) rotate(-5deg); filter: brightness(1.2); }
    60% { transform: scale(0.98) rotate(4deg); }
    75% { transform: scale(1.03) rotate(-3deg); }
    100% { transform: scale(1) rotate(0deg); }
}

.streak-crack-shiver {
    animation: streak-crack-shiver 0.5s ease-in-out infinite;
}

/* Extinguish Pop Keyframes */
@keyframes streak-extinguished-pop {
    0% { transform: scale(1.2); filter: grayscale(0) brightness(1.3); }
    40% { transform: scale(0.85); filter: grayscale(0.7) brightness(0.9); }
    70% { transform: scale(1.05); filter: grayscale(1); }
    100% { transform: scale(1); filter: grayscale(0); }
}

.streak-extinguished-pop {
    animation: streak-extinguished-pop 0.55s ease-out forwards;
}

/* Crack Line Animation */
.crack-svg-line {
    animation: crack-line-fade 0.5s ease-out forwards;
}

@keyframes crack-line-fade {
    0% { opacity: 0; transform: scale(0.9); }
    50% { opacity: 1; transform: scale(1.05); }
    100% { opacity: 1; transform: scale(1); }
}

/* Rising Smoke Puffs */
.smoke-puff {
    position: absolute;
    opacity: 0;
    pointer-events: none;
    font-size: 1.25rem;
}

.smoke-puff-1 {
    top: 30%;
    left: 45%;
    animation: float-smoke-1 2.2s ease-out infinite 0.2s;
}

.smoke-puff-2 {
    top: 35%;
    left: 30%;
    animation: float-smoke-2 2.5s ease-out infinite 0.6s;
}

.smoke-puff-3 {
    top: 32%;
    left: 60%;
    animation: float-smoke-3 2.1s ease-out infinite 0.9s;
}

@keyframes float-smoke-1 {
    0% { transform: translateY(0) scale(0.6) rotate(0deg); opacity: 0; }
    20% { opacity: 0.85; transform: translateY(-12px) scale(1) rotate(-8deg); }
    80% { opacity: 0.3; transform: translateY(-42px) scale(1.4) rotate(12deg); }
    100% { transform: translateY(-56px) scale(1.7) rotate(20deg); opacity: 0; }
}

@keyframes float-smoke-2 {
    0% { transform: translateY(0) scale(0.5) rotate(0deg); opacity: 0; }
    25% { opacity: 0.75; transform: translateY(-15px) scale(0.9) rotate(10deg); }
    75% { opacity: 0.25; transform: translateY(-45px) scale(1.3) rotate(-14deg); }
    100% { transform: translateY(-60px) scale(1.6) rotate(-22deg); opacity: 0; }
}

@keyframes float-smoke-3 {
    0% { transform: translateY(0) scale(0.6) rotate(0deg); opacity: 0; }
    20% { opacity: 0.8; transform: translateY(-10px) scale(1) rotate(6deg); }
    70% { opacity: 0.2; transform: translateY(-38px) scale(1.3) rotate(-10deg); }
    100% { transform: translateY(-52px) scale(1.5) rotate(-16deg); opacity: 0; }
}

/* Number Drop Keyframes */
@keyframes number-drop {
    0% { transform: translateY(-10px) scale(1.2); opacity: 0; }
    40% { transform: translateY(4px) scale(0.95); opacity: 1; }
    70% { transform: translateY(-2px) scale(1.05); }
    100% { transform: translateY(0) scale(1); opacity: 1; }
}

.number-drop-animate {
    animation: number-drop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

button:hover .animate-spin-hover {
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
