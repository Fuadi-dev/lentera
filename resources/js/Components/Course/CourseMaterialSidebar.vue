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
import {
    Sidebar,
    SidebarContent,
    SidebarGroup,
    SidebarGroupContent,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/Components/ui/sidebar';

const props = defineProps({
    course: { type: Object, required: true },
    classNumber: { type: [Number, String], required: true },
    material: { type: Object, required: true },
    completedUnitIds: { type: Array, default: () => [] },
});

function isUnitCompleted(unitId) {
    return props.completedUnitIds.includes(unitId);
}

// Dapatkan kelas saat ini berdasarkan classNumber
const currentClass = computed(() => {
    return props.course.classes?.find(c => Number(c.number) === Number(props.classNumber)) || {};
});

// Cari level yang menaungi material saat ini, lalu kembalikan chapters dari level tersebut saja
const chapters = computed(() => {
    if (!currentClass.value.levels) return [];
    
    // Temukan level mana yang memiliki material ini
    for (const level of currentClass.value.levels) {
        if (!level.chapters) continue;
        for (const chapter of level.chapters) {
            if (!chapter.units) continue;
            if (chapter.units.some(u => String(u.id) === String(props.material.id))) {
                return level.chapters; // Hanya return chapter dari level ini
            }
        }
    }
    
    // Fallback: kembalikan level pertama jika tidak ketemu (seharusnya tidak terjadi)
    return currentClass.value.levels[0]?.chapters || [];
});

const isCurrentLevelCompleted = computed(() => {
    const allUnits = chapters.value.flatMap(c => c.units || []);
    if (!allUnits.length) return false;
    return allUnits.every(u => isUnitCompleted(u.id));
});

// Mengecek apakah suatu chapter berisi materi (unit) yang sedang dibuka
function isChapterActive(chapter) {
    if (!chapter.units) return false;
    return chapter.units.some(u => u.id === props.material.id);
}

// Menentukan ikon berdasarkan tipe unit
function unitIcon(type) {
    if (type === 'video') return PhVideoCamera;
    if (type === 'quiz') return PhListChecks;
    return PhBookOpen;
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
    <Sidebar
        variant="sidebar"
        class="!sticky !top-6 !bottom-auto !h-fit max-h-[calc(100vh-3rem)] w-64 min-w-64 overflow-hidden rounded-xl border border-border bg-card"
    >
        <SidebarHeader class="border-b border-border">
            <SidebarMenuButton
                size="lg"
                class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
            >
                <div
                    class="flex items-center justify-center rounded-lg aspect-square size-8 bg-sidebar-primary text-sidebar-primary-foreground"
                >
                    <PhBookOpen class="text-primary" :size="20" aria-hidden="true" />
                </div>
                <div
                    class="grid flex-1 text-sm leading-tight text-left group-data-[collapsible=icon]:hidden"
                >
                    <p class="min-w-0 text-xs truncate text-muted-foreground">{{ course.title }}</p>
                    <p class="min-w-0 font-semibold truncate">Kelas {{ classNumber }}</p>
                </div>
            </SidebarMenuButton>
        </SidebarHeader>

        <div
            v-if="isCurrentLevelCompleted"
            class="mx-3 mt-3 p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2 shadow-sm"
        >
            <PhCheckCircle :size="16" weight="fill" class="text-emerald-600 shrink-0" />
            <span class="font-medium">Semua materi selesai! Level selanjutnya terbuka.</span>
        </div>

        <SidebarContent>
            <SidebarGroup>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <Collapsible
                            v-for="chapter in chapters"
                            :key="chapter.id"
                            as-child
                            :default-open="isChapterActive(chapter)"
                            class="group/collapsible"
                        >
                            <SidebarMenuItem>
                                <CollapsibleTrigger as-child>
                                    <SidebarMenuButton
                                        :is-active="isChapterActive(chapter)"
                                        :aria-label="`Buka materi Chapter ${chapter.number}`"
                                        :tooltip="`Chapter ${chapter.number}: ${chapter.title}`"
                                    >
                                        <span
                                            class="min-w-0 truncate group-data-[collapsible=icon]:hidden"
                                        >
                                            Chapter {{ chapter.number }}
                                        </span>
                                        <PhCaretRight
                                            class="ml-auto size-4 transition-transform group-data-[collapsible=icon]:hidden group-data-[state=open]/collapsible:rotate-90"
                                            aria-hidden="true"
                                        />
                                    </SidebarMenuButton>
                                </CollapsibleTrigger>

                                <CollapsibleContent>
                                    <SidebarMenuSub>
                                        <SidebarMenuSubItem
                                            v-for="unit in (chapter.units || [])"
                                            :key="unit.id"
                                        >
                                            <SidebarMenuSubButton
                                                :as-child="true"
                                                :is-active="unit.id === material.id"
                                            >
                                                <Link :href="unitHref(unit)">
                                                    <PhCheckCircle
                                                        v-if="isUnitCompleted(unit.id)"
                                                        class="size-4 text-green-600"
                                                        weight="fill"
                                                        aria-label="Selesai"
                                                    />
                                                    <component
                                                        v-else
                                                        :is="unitIcon(unit.type)"
                                                        class="size-4"
                                                        aria-hidden="true"
                                                    />
                                                    <span
                                                        class="min-w-0 truncate group-data-[collapsible=icon]:hidden"
                                                        :class="isUnitCompleted(unit.id) ? 'text-green-600' : ''"
                                                    >
                                                        {{ unit.title }}
                                                    </span>
                                                </Link>
                                            </SidebarMenuSubButton>
                                        </SidebarMenuSubItem>
                                        
                                        <SidebarMenuSubItem v-if="chapter.finalTest">
                                            <SidebarMenuSubButton
                                                disabled
                                                :aria-label="`${chapter.finalTest.title} terkunci`"
                                            >
                                                <PhGraduationCap
                                                    class="size-4"
                                                    aria-hidden="true"
                                                />
                                                <span
                                                    class="min-w-0 truncate group-data-[collapsible=icon]:hidden"
                                                >
                                                    {{ chapter.finalTest.title }}
                                                </span>
                                                <PhLock class="ml-auto size-4" aria-hidden="true" />
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
</template>
