<script setup>
import { ref, watch, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import confetti from 'canvas-confetti';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/Components/ui/dialog';
import Button from '@/Components/ui/button/Button.vue';
import { PhFire, PhSparkle } from '@phosphor-icons/vue';

const page = usePage();
const isOpen = ref(false);
const streakData = ref(null);

function fireConfetti() {
    try {
        confetti({
            particleCount: 80,
            spread: 70,
            origin: { y: 0.6 },
            colors: ['#f97316', '#f59e0b', '#ef4444', '#10b981', '#6366f1'],
        });

        setTimeout(() => {
            confetti({
                particleCount: 45,
                angle: 60,
                spread: 55,
                origin: { x: 0.1, y: 0.65 },
                colors: ['#f97316', '#fb923c', '#fdba74'],
            });
            confetti({
                particleCount: 45,
                angle: 120,
                spread: 55,
                origin: { x: 0.9, y: 0.65 },
                colors: ['#f97316', '#fb923c', '#fdba74'],
            });
        }, 180);
    } catch {
        // Fallback gracefully if canvas is unavailable
    }
}

function openCelebration(data) {
    if (isOpen.value) {
        return;
    }

    streakData.value = data;
    isOpen.value = true;
    nextTick(() => {
        fireConfetti();
    });
}

watch(
    () => page.props.flash?.streak_award,
    (newAward) => {
        if (newAward && newAward.streak_increased) {
            openCelebration(newAward);
        }
    },
    { immediate: true, deep: true }
);

// Cadangan: jika flash tidak terbawa, perubahan status streak menjadi aktif hari ini tetap memicu animasi.
watch(
    () => page.props.auth?.streak_active_today,
    (isActiveToday, wasActiveToday) => {
        if (isActiveToday === true && wasActiveToday === false) {
            openCelebration({
                streak_increased: true,
                current_streak: page.props.auth?.streak ?? 1,
                longest_streak: null,
            });
        }
    }
);

function closeModal() {
    isOpen.value = false;
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="(val) => (isOpen = val)">
        <DialogContent class="max-w-sm sm:max-w-md p-6 overflow-hidden text-center border-orange-200 bg-gradient-to-b from-orange-50/80 via-white to-amber-50/50 shadow-2xl rounded-2xl">
            <div class="relative py-4 flex flex-col items-center">
                <!-- Glowing Backdrop Halo -->
                <div class="relative flex items-center justify-center w-28 h-28 my-2">
                    <div class="absolute inset-0 rounded-full bg-orange-400/25 blur-2xl animate-pulse" aria-hidden="true"></div>
                    <div class="absolute inset-2 rounded-full bg-amber-300/30 blur-lg" aria-hidden="true"></div>
                    <img
                        src="/images/ui/streak.png"
                        alt="Streak Api Belajar"
                        class="relative w-24 h-24 object-contain transition-transform duration-300 transform hover:scale-110 drop-shadow-md"
                    />
                </div>

                <DialogHeader class="items-center text-center space-y-2 mt-2">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-100 border border-orange-200/80 text-orange-700 text-xs font-bold tracking-wide uppercase">
                        <PhFire :size="16" weight="fill" class="text-orange-600 animate-bounce" />
                        <span>Streak Meningkat!</span>
                        <PhSparkle :size="14" weight="fill" class="text-amber-500" />
                    </div>

                    <DialogTitle class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">
                        {{ streakData?.current_streak ?? 1 }} Hari Berturut-turut!
                    </DialogTitle>

                    <DialogDescription class="text-sm sm:text-base text-gray-600 font-medium max-w-xs mx-auto leading-relaxed">
                        Luar biasa! Kamu telah menyelesaikan aktivitas belajar hari ini. Terus jaga apimu agar tidak padam besok ya! 🔥
                    </DialogDescription>
                </DialogHeader>

                <!-- Longest streak / record info if applicable -->
                <div v-if="streakData?.longest_streak" class="mt-4 px-4 py-2 rounded-xl bg-orange-50/90 border border-orange-100 flex items-center gap-2 text-xs font-semibold text-orange-800">
                    <span class="text-orange-500">🏆</span>
                    <span>Rekor streak terpanjangmu: <strong>{{ streakData.longest_streak }} hari</strong></span>
                </div>

                <div class="w-full mt-6">
                    <Button
                        type="button"
                        class="w-full py-6 text-base font-bold text-white shadow-lg shadow-orange-500/25 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 rounded-xl transition-all duration-200 hover:shadow-orange-500/40 transform active:scale-[0.98]"
                        @click="closeModal"
                    >
                        Mantap, Lanjutkan! 🚀
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
