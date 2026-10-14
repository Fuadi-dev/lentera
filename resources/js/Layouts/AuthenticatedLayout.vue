<script setup>
import { ref, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/ui/tooltip';
import { Link, usePage } from '@inertiajs/vue3';
import StreakCelebrationModal from '@/Components/Course/StreakCelebrationModal.vue';

const page = usePage();
const showingNavigationDropdown = ref(false);
const streakBump = ref(false);

function triggerStreakBump() {
    streakBump.value = false;
    requestAnimationFrame(() => {
        streakBump.value = true;
        setTimeout(() => (streakBump.value = false), 1600);
    });
}

watch(
    () => page.props.auth?.streak,
    (newStreak, oldStreak) => {
        if (oldStreak !== undefined && (newStreak ?? 0) > (oldStreak ?? 0)) {
            triggerStreakBump();
        }
    }
);

watch(
    () => page.props.auth?.streak_active_today,
    (isActiveToday, wasActiveToday) => {
        if (isActiveToday === true && wasActiveToday === false) {
            triggerStreakBump();
        }
    }
);

watch(
    () => page.props.flash?.streak_award,
    (award) => {
        if (award && award.streak_increased) {
            triggerStreakBump();
        }
    }
);
</script>

<template>
    <div>
        <div class="min-h-screen bg-secondary">
            <nav class="sticky top-0 z-40 bg-white border-b border-gray-100">
                <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="flex items-center shrink-0">
                                <Link href="/">
                                    <ApplicationLogo
                                        class="block w-auto h-12 py-2 text-gray-800 fill-current"
                                    />
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Beranda
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <TooltipProvider v-if="$page.props.auth.user?.role === 'child'">
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <div class="flex items-center cursor-pointer select-none">
                                            <img
                                                :src="$page.props.auth.streak_active_today ? '/images/ui/streak.png' : '/images/ui/not streak.png'"
                                                alt="Streak"
                                                class="object-contain w-11 h-11 transition-all duration-300"
                                                :class="[
                                                    $page.props.auth.streak_active_today ? 'drop-shadow-sm hover:scale-105' : '',
                                                    streakBump ? 'streak-flame-bump' : '',
                                                ]"
                                            />
                                            <span
                                                :class="[
                                                    'text-base font-bold transition-colors duration-200',
                                                    $page.props.auth.streak_active_today ? 'text-orange-500' : 'text-gray-400',
                                                    streakBump ? 'streak-number-bump' : '',
                                                ]"
                                            >
                                                {{ $page.props.auth.streak ?? 0 }}
                                            </span>
                                        </div>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <span v-if="$page.props.auth.streak_active_today">
                                            Streak {{ $page.props.auth.streak }} hari (Aktif hari ini! 🔥)
                                        </span>
                                        <span v-else-if="($page.props.auth.streak ?? 0) > 0">
                                            Streak {{ $page.props.auth.streak }} hari (Belajar hari ini untuk menyalakan api!)
                                        </span>
                                        <span v-else>
                                            Belum ada streak (Yuk belajar hari ini untuk menyalakan api!)
                                        </span>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>

                            <div v-if="$page.props.auth.user?.role === 'child'" class="w-px h-8 mx-3 bg-gray-200" aria-hidden="true"></div>

                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            Profil
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="flex items-center -me-2 sm:hidden">
                            <TooltipProvider v-if="$page.props.auth.user?.role === 'child'">
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <div class="flex items-center me-2 select-none">
                                            <img
                                                :src="$page.props.auth.streak_active_today ? '/images/ui/streak.png' : '/images/ui/not streak.png'"
                                                alt="Streak"
                                                class="object-contain w-9 h-9 transition-all duration-300"
                                                :class="streakBump ? 'streak-flame-bump' : ''"
                                            />
                                            <span
                                                :class="[
                                                    'text-sm font-bold transition-colors duration-200',
                                                    $page.props.auth.streak_active_today ? 'text-orange-500' : 'text-gray-400',
                                                    streakBump ? 'streak-number-bump' : '',
                                                ]"
                                            >
                                                {{ $page.props.auth.streak ?? 0 }}
                                            </span>
                                        </div>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <span v-if="$page.props.auth.streak_active_today">
                                            Streak {{ $page.props.auth.streak }} hari (Aktif hari ini! 🔥)
                                        </span>
                                        <span v-else-if="($page.props.auth.streak ?? 0) > 0">
                                            Streak {{ $page.props.auth.streak }} hari (Belajar hari ini untuk menyalakan api!)
                                        </span>
                                        <span v-else>
                                            Belum ada streak (Yuk belajar hari ini untuk menyalakan api!)
                                        </span>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>

                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="w-6 h-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Beranda
                        </ResponsiveNavLink>
                    </div>

                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header class="bg-secondary" v-if="$slots.header">
                <div class="px-4 pt-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>

        <StreakCelebrationModal />
    </div>
</template>

<style scoped>
@keyframes streak-flame-bump {
    0% { transform: scale(1) rotate(0deg); filter: drop-shadow(0 0 0 rgba(249, 115, 22, 0)); }
    25% { transform: scale(1.45) rotate(-12deg); filter: drop-shadow(0 0 10px rgba(249, 115, 22, 0.9)); }
    50% { transform: scale(1.3) rotate(10deg); filter: drop-shadow(0 0 14px rgba(249, 115, 22, 0.8)); }
    75% { transform: scale(1.2) rotate(-6deg); filter: drop-shadow(0 0 8px rgba(249, 115, 22, 0.6)); }
    100% { transform: scale(1) rotate(0deg); filter: drop-shadow(0 0 0 rgba(249, 115, 22, 0)); }
}

@keyframes streak-number-bump {
    0% { transform: translateY(0) scale(1); }
    30% { transform: translateY(-8px) scale(1.5); }
    60% { transform: translateY(2px) scale(1.15); }
    100% { transform: translateY(0) scale(1); }
}

.streak-flame-bump {
    animation: streak-flame-bump 1.2s ease-out;
}

.streak-number-bump {
    display: inline-block;
    animation: streak-number-bump 1s ease-out;
}
</style>
