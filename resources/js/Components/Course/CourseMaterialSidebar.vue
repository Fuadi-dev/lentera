<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    PhBookOpen,
    PhCaretRight,
    PhCheckCircle,
    PhGraduationCap,
    PhListChecks,
    PhLock,
    PhVideoCamera,
} from '@phosphor-icons/vue';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/Components/ui/collapsible';

const props = defineProps({
    course: { type: Object, required: true },
    classNumber: { type: [Number, String], required: true },
    material: { type: Object, required: true },
    completedUnitIds: { type: Array, default: () => [] },
});

defineEmits(['selectUnit']);

function isUnitCompleted(unitId) {
    return props.completedUnitIds.includes(unitId);
}

// Dapatkan kelas saat ini berdasarkan classNumber
const currentClass = computed(() => {
    return props.course.classes?.find((c) => Number(c.number) === Number(props.classNumber)) || {};
});

// Cari level yang menaungi material saat ini, lalu kembalikan chapters dari level tersebut saja
const chapters = computed(() => {
    if (!currentClass.value.levels) return [];

    // Temukan level mana yang memiliki material ini
    for (const level of currentClass.value.levels) {
        if (!level.chapters) continue;
        for (const chapter of level.chapters) {
            if (!chapter.units) continue;
            if (chapter.units.some((u) => String(u.id) === String(props.material.id))) {
                return level.chapters; // Hanya return chapter dari level ini
            }
        }
    }

    // Fallback: kembalikan level pertama jika tidak ketemu
    return currentClass.value.levels[0]?.chapters || [];
});

const isCurrentLevelCompleted = computed(() => {
    const allUnits = chapters.value.flatMap((c) => c.units || []);
    if (!allUnits.length) return false;
    return allUnits.every((u) => isUnitCompleted(u.id));
});

// Mengecek apakah suatu chapter berisi materi (unit) yang sedang dibuka
function isChapterActive(chapter) {
    if (!chapter.units) return false;
    return chapter.units.some((u) => u.id === props.material.id);
}

// Menentukan ikon berdasarkan tipe unit
function unitIcon(type) {
    if (type === 'video') return PhVideoCamera;
    if (type === 'quiz') return PhListChecks;
    return PhBookOpen;
}

// Format judul chapter agar tidak duplikat seperti "Chapter 1: Chapter 1"
function formatChapterTitle(chapter) {
    if (!chapter) return '';
    const number = chapter.number ?? '';
    const title = (chapter.title || '').trim();

    if (!title) {
        return number ? `Chapter ${number}` : 'Chapter';
    }

    if (number) {
        // Jika title bernilai "Chapter X" atau "Bab X" (sama persis / berulang)
        if (new RegExp(`^(chapter|bab)\\s*${number}$`, 'i').test(title)) {
            return `Chapter ${number}`;
        }   

        // Jika title sudah diawali "Chapter X:" atau "Bab X -"
        if (new RegExp(`^(chapter|bab)\\s*${number}[:\\-\\s]+`, 'i').test(title)) {
            return title;
        }

        // Jika title berupa topik khusus yang berbeda dari nama nomor bab
        return `Chapter ${number}: ${title}`;
    }

    return title;
}

// Membuat URL unit
function unitHref(unit) {
    return route('courses.material', {
        course: props.course.id,
        class: props.classNumber,
        material: unit.id,
    });
}
</script>

<template>
    <div class="flex flex-col h-full w-full bg-white overflow-hidden text-slate-800">
        <!-- Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between pr-14 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="flex items-center justify-center rounded-xl size-10 bg-primary/10 text-primary shrink-0">
                    <PhBookOpen :size="20" weight="bold" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground truncate">{{ course.title }}</p>
                    <p class="font-bold text-sm text-foreground truncate">Kelas {{ classNumber }}</p>
                </div>
            </div>
        </div>

        <!-- Level Completed Banner -->
        <div
            v-if="isCurrentLevelCompleted"
            class="mx-4 mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2 shadow-sm shrink-0"
        >
            <PhCheckCircle :size="16" weight="fill" class="text-emerald-600 shrink-0" />
            <span class="font-medium">Semua materi selesai! Level selanjutnya terbuka.</span>
        </div>

        <!-- Chapters List (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-4 space-y-2.5">
            <div class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground px-1 mb-1">
                Daftar Bab & Materi
            </div>

            <Collapsible
                v-for="chapter in chapters"
                :key="chapter.id"
                :default-open="isChapterActive(chapter)"
                class="group/collapsible rounded-2xl overflow-hidden border border-slate-100 bg-white"
            >
                <CollapsibleTrigger
                    class="w-full flex items-center justify-between p-3.5 text-left font-semibold text-xs sm:text-sm transition-colors hover:bg-slate-50 cursor-pointer"
                    :class="isChapterActive(chapter) ? 'bg-orange-50/70 text-primary font-bold' : 'text-slate-700'"
                >
                    <span class="truncate pr-2">{{ formatChapterTitle(chapter) }}</span>
                    <PhCaretRight
                        class="size-4 shrink-0 transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90 text-slate-400"
                        aria-hidden="true"
                    />
                </CollapsibleTrigger>

                <CollapsibleContent class="bg-slate-50/60 p-2 space-y-1 border-t border-slate-100/80">
                    <Link
                        v-for="unit in (chapter.units || [])"
                        :key="unit.id"
                        :href="unitHref(unit)"
                        class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-medium transition-all"
                        :class="[
                            unit.id === material.id
                                ? 'bg-primary text-white font-bold shadow-sm'
                                : 'text-slate-600 hover:bg-white hover:text-slate-900',
                            isUnitCompleted(unit.id) && unit.id !== material.id ? 'text-green-700' : ''
                        ]"
                        @click="$emit('selectUnit', unit)"
                    >
                        <PhCheckCircle
                            v-if="isUnitCompleted(unit.id)"
                            class="size-4 shrink-0"
                            :class="unit.id === material.id ? 'text-white' : 'text-green-600'"
                            weight="fill"
                            aria-label="Selesai"
                        />
                        <component
                            v-else
                            :is="unitIcon(unit.type)"
                            class="size-4 shrink-0"
                            aria-hidden="true"
                        />
                        <span class="truncate flex-1">{{ unit.title }}</span>
                    </Link>

                    <div
                        v-if="chapter.finalTest"
                        class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-medium text-slate-400 opacity-60 cursor-not-allowed"
                    >
                        <PhGraduationCap class="size-4 shrink-0" aria-hidden="true" />
                        <span class="truncate flex-1">{{ chapter.finalTest.title }}</span>
                        <PhLock class="ml-auto size-3.5 shrink-0" aria-hidden="true" />
                    </div>
                </CollapsibleContent>
            </Collapsible>
        </div>
    </div>
</template>
