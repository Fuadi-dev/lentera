<script setup>
import { Link } from '@inertiajs/vue3';
import {
    PhBookOpen,
    PhCaretRight,
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
});

const courseClasses = () => props.course.classes ?? [];

function classUnits(courseClass) {
    if (courseClass.section) {
        return courseClass.section.flatMap((section) => section.unit ?? []);
    }

    return (courseClass.levels ?? []).flatMap((level) =>
        (level.chapters ?? []).flatMap((chapter) => chapter.units ?? [])
    );
}

function classFinalTest(courseClass) {
    if (courseClass.finalTest) {
        return courseClass.finalTest;
    }

    return courseClass.levels?.[0]?.chapters?.[0]?.finalTest;
}

function isClassUnlocked(courseClass) {
    return courseClass.number <= Number(props.classNumber);
}

function unitIcon(type) {
    if (type === 'video') {
        return PhVideoCamera;
    }

    if (type === 'quiz') {
        return PhListChecks;
    }

    return PhBookOpen;
}

function unitHref(courseClass, unit) {
    return route('courses.material', {
        course: props.course.id,
        class: courseClass.number,
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
                    <p class="min-w-0 font-semibold truncate">Chapter {{ classNumber }}</p>
                </div>
            </SidebarMenuButton>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <Collapsible
                            v-for="courseClass in courseClasses()"
                            :key="courseClass.id"
                            as-child
                            :default-open="Number(classNumber) === courseClass.number"
                            class="group/collapsible"
                        >
                            <SidebarMenuItem>
                                <CollapsibleTrigger as-child>
                                    <SidebarMenuButton
                                        :is-active="Number(classNumber) === courseClass.number"
                                        :aria-label="`Buka materi Chapter ${courseClass.number}`"
                                        :tooltip="`Chapter ${courseClass.number}`"
                                    >
                                        <span
                                            class="min-w-0 truncate group-data-[collapsible=icon]:hidden"
                                        >
                                            Chapter {{ courseClass.number }}
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
                                            v-for="unit in classUnits(courseClass)"
                                            :key="unit.id"
                                        >
                                            <SidebarMenuSubButton
                                                v-if="isClassUnlocked(courseClass)"
                                                :as-child="true"
                                                :is-active="unit.id === material.id"
                                            >
                                                <Link :href="unitHref(courseClass, unit)">
                                                    <component
                                                        :is="unitIcon(unit.type)"
                                                        class="size-4"
                                                        aria-hidden="true"
                                                    />
                                                    <span
                                                        class="min-w-0 truncate group-data-[collapsible=icon]:hidden"
                                                    >
                                                        {{ unit.title }}
                                                    </span>
                                                </Link>
                                            </SidebarMenuSubButton>
                                            <SidebarMenuSubButton
                                                v-else
                                                disabled
                                                :aria-label="`${unit.title} terkunci`"
                                            >
                                                <PhLock class="size-4" aria-hidden="true" />
                                                <span
                                                    class="min-w-0 truncate group-data-[collapsible=icon]:hidden"
                                                >
                                                    {{ unit.title }}
                                                </span>
                                            </SidebarMenuSubButton>
                                        </SidebarMenuSubItem>
                                        <SidebarMenuSubItem v-if="classFinalTest(courseClass)">
                                            <SidebarMenuSubButton
                                                disabled
                                                :aria-label="`${classFinalTest(courseClass).title} terkunci`"
                                            >
                                                <PhGraduationCap
                                                    class="size-4"
                                                    aria-hidden="true"
                                                />
                                                <span
                                                    class="min-w-0 truncate group-data-[collapsible=icon]:hidden"
                                                >
                                                    {{ classFinalTest(courseClass).title }}
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
