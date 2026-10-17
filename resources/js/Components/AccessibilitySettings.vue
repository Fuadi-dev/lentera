<script setup lang="ts">
import { computed } from 'vue';
import { Minus, Plus, RotateCcw, Type, X } from 'lucide-vue-next';
import { PhPersonArmsSpread } from '@phosphor-icons/vue';
import { useAccessibilitySettings } from '@/Composables/useAccessibilitySettings';
import { Button } from '@/Components/ui/button';
import { Switch } from '@/Components/ui/switch';
import AccessibilitySlider from '@/Components/AccessibilitySlider.vue';
import {
    Drawer,
    DrawerClose,
    DrawerContent,
    DrawerDescription,
    DrawerHeader,
    DrawerTitle,
    DrawerTrigger,
} from '@/Components/ui/drawer';

const { fontScale, dyslexiaFont, isDefault, resetSettings } = useAccessibilitySettings();

const sliderValue = computed({
    get: () => [fontScale.value],
    set: (value: number[]) => {
        fontScale.value = value[0] ?? 100;
    },
});

const fontSizeOptions: { value: number; label: string; icon: typeof Minus }[] = [
    { value: 90, label: 'Kecil', icon: Minus },
    { value: 100, label: 'Normal', icon: Type },
    { value: 130, label: 'Besar', icon: Plus },
];

function changeFontScale(amount: number): void {
    fontScale.value = Math.min(130, Math.max(90, fontScale.value + amount));
}
</script>

<template>
    <Drawer swipe-direction="right">
        <DrawerTrigger
            class="fixed right-0 z-30 flex items-center justify-center text-white transition bg-orange-600 rounded-l-full shadow-lg top-36 sm:top-20 size-12 hover:bg-orange-700 focus:outline-none focus:ring-4 focus:ring-orange-200"
            aria-label="Buka pengaturan aksesibilitas"
        >
            <PhPersonArmsSpread class="size-6" weight="bold" aria-hidden="true" />
        </DrawerTrigger>

        <DrawerContent class="p-5 bg-white border-0 outline-none ring-0 shadow-2xl">
            <DrawerHeader class="p-0">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-orange-600"
                        >
                            Tampilan
                        </p>
                        <DrawerTitle class="mt-1 text-lg font-bold text-stone-900">
                            Pengaturan aksesibilitas
                        </DrawerTitle>
                        <DrawerDescription class="mt-1 text-sm text-stone-500">
                            Sesuaikan tampilan agar lebih nyaman dibaca.
                        </DrawerDescription>
                    </div>
                    <DrawerClose
                        class="rounded-lg p-1.5 text-stone-500 transition hover:bg-stone-100 hover:text-stone-900 focus:outline-none focus:ring-2 focus:ring-orange-500"
                        aria-label="Tutup pengaturan aksesibilitas"
                    >
                        <X class="size-5" aria-hidden="true" />
                    </DrawerClose>
                </div>
            </DrawerHeader>

            <div class="mt-6">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-sm font-semibold text-stone-800">Ukuran teks</p>
                    <output class="text-sm font-bold text-orange-700" for="font-size-slider">
                        {{ fontScale }}%
                    </output>
                </div>
                <div class="flex items-center gap-3 mt-4">
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="shrink-0 text-stone-500 hover:text-orange-700"
                        aria-label="Perkecil ukuran teks"
                        :disabled="fontScale <= 90"
                        @click="changeFontScale(-5)"
                    >
                        <Minus class="size-4" aria-hidden="true" />
                    </Button>
                    <AccessibilitySlider
                        id="font-size-slider"
                        v-model="sliderValue"
                        :min="90"
                        :max="130"
                        :step="5"
                        :aria-label="`Ukuran teks ${fontScale}%`"
                    />
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="shrink-0 text-stone-500 hover:text-orange-700"
                        aria-label="Perbesar ukuran teks"
                        :disabled="fontScale >= 130"
                        @click="changeFontScale(5)"
                    >
                        <Plus class="size-4" aria-hidden="true" />
                    </Button>
                </div>
                <div
                    class="grid grid-cols-3 gap-2 mt-3"
                    role="group"
                    aria-label="Preset ukuran teks"
                >
                    <Button
                        v-for="option in fontSizeOptions"
                        :key="option.value"
                        type="button"
                        variant="outline"
                        size="sm"
                        class="text-xs font-semibold"
                        :class="
                            fontScale === option.value
                                ? 'border-orange-600 bg-orange-50 text-orange-700'
                                : 'border-stone-200 text-stone-600 hover:border-orange-300 hover:bg-orange-50/50'
                        "
                        :aria-pressed="fontScale === option.value"
                        @click="fontScale = option.value"
                    >
                        <component :is="option.icon" class="size-3.5" aria-hidden="true" />
                        {{ option.label }}
                    </Button>
                </div>
            </div>

            <div
                class="flex items-center justify-between gap-4 pt-5 mt-6 border-t border-stone-100"
            >
                <div>
                    <p class="text-sm font-semibold text-stone-800">Font ramah disleksia</p>
                    <p class="mt-0.5 text-xs text-stone-500">Huruf lebih mudah dibedakan</p>
                </div>
                <Switch v-model="dyslexiaFont" aria-label="Aktifkan font ramah disleksia" />
            </div>

            <Button
                variant="ghost"
                size="xs"
                class="mt-6 text-stone-500 hover:text-orange-700"
                :disabled="isDefault"
                @click="resetSettings"
            >
                <RotateCcw class="size-3.5" aria-hidden="true" />
                Kembalikan ke pengaturan awal
            </Button>
        </DrawerContent>
    </Drawer>
</template>
