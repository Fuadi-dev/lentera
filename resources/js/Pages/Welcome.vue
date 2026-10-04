<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import LoginIcon from '@/Components/LoginIcon.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
    },
    phpVersion: {
        type: String,
    },
});

const isMobileMenuOpen = ref(false);

const navLinks = [
    { label: 'Beranda', href: '#beranda' },
    { label: 'Tentang', href: '#tentang' },
    { label: 'Fitur', href: '#fitur' },
    { label: 'Panduan', href: '#panduan' },
];
</script>

<template>
    <Head title="Lentera - Belajar Mandiri Dengan Seru" />

    <div
        id="beranda"
        class="relative flex h-dvh flex-col overflow-hidden bg-white"
    >
        <header
            class="relative z-20 mx-auto flex w-full max-w-6xl shrink-0 items-center justify-between px-4 py-4 sm:px-6 sm:py-5"
        >
            <Link href="/" class="flex items-center">
                <img
                    src="/images/brand/logo-lentera.png"
                    alt="Lentera"
                    class="h-10 w-auto"
                />
            </Link>

            <nav class="hidden items-center gap-8 md:flex">
                <a
                    v-for="item in navLinks"
                    :key="item.label"
                    :href="item.href"
                    class="text-base font-medium text-gray-700 transition-colors hover:text-primary"
                >
                    {{ item.label }}
                </a>
            </nav>

            <div class="hidden items-center gap-2 sm:gap-3 md:flex">
                <Button
                    v-if="$page.props.auth.user"
                    :as="Link"
                    :href="route('dashboard')"
                    size="lg"
                    class="rounded-xl px-4 sm:px-5"
                >
                    Dashboard
                </Button>
                <Button
                    v-else
                    :as="Link"
                    :href="route('login')"
                    size="lg"
                    class="gap-2 rounded-xl px-4 sm:px-5"
                >
                    <span class="hidden sm:inline">Masuk</span>
                    <span class="sm:hidden">Masuk</span>
                    <LoginIcon class="size-5" />
                </Button>
            </div>

            <!-- Hamburger Button -->
            <button
                class="block p-2 text-gray-700 md:hidden"
                @click="isMobileMenuOpen = !isMobileMenuOpen"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </header>

        <!-- Mobile Menu Overlay -->
        <div
            v-if="isMobileMenuOpen"
            class="absolute inset-x-0 top-[72px] z-20 flex flex-col items-center gap-6 bg-white pb-8 pt-4 shadow-lg md:hidden"
        >
            <a
                v-for="item in navLinks"
                :key="item.label"
                :href="item.href"
                @click="isMobileMenuOpen = false"
                class="text-lg font-medium text-gray-700 transition-colors hover:text-primary"
            >
                {{ item.label }}
            </a>

            <div class="mt-2 w-full px-6">
                <Button
                    v-if="$page.props.auth.user"
                    :as="Link"
                    :href="route('dashboard')"
                    size="lg"
                    class="w-full rounded-xl px-4"
                >
                    Dashboard
                </Button>
                <Button
                    v-else
                    :as="Link"
                    :href="route('login')"
                    size="lg"
                    class="flex w-full items-center justify-center gap-2 rounded-xl px-4"
                >
                    <span>Masuk</span>
                    <LoginIcon class="size-5" />
                </Button>
            </div>
        </div>

        <main class="relative z-10 mx-auto w-full max-w-4xl shrink-0 px-4 pt-1 text-center sm:px-6">
            <h1
                class="text-4xl font-bold leading-tight text-primary sm:text-5xl lg:text-[3.75rem]"
            >
                Belajar Mandiri<br />Dengan Seru
            </h1>
            <p class="mx-auto mt-3 max-w-xl text-lg text-gray-700 md:text-xl">
                Dengan Lentera, anak mendapatkan pengalaman belajar yang lebih
                terarah
            </p>

            <div class="mt-4 flex flex-col items-stretch justify-center gap-3 px-4 sm:flex-row sm:items-center sm:px-0">
                <Button
                    as="a"
                    href="#fitur"
                    variant="outline"
                    size="lg"
                    class="rounded-xl px-6 text-foreground"
                >
                    Pelajari
                </Button>
                <Button
                    :as="Link"
                    :href="canRegister ? route('register') : route('login')"
                    size="lg"
                    class="gap-2 rounded-xl px-5"
                >
                    <span>Coba Gratis</span>
                    <LoginIcon class="size-5" />
                </Button>
            </div>
        </main>

        <section class="relative mt-1 flex min-h-0 flex-1 justify-center overflow-hidden">
            <img
                src="/images/ui/hero.png"
                alt="Tampilan aplikasi Lentera di ponsel"
                class="relative z-10 h-full w-auto max-w-full origin-bottom scale-[2.5] object-contain object-bottom px-4 sm:scale-[1.4] [mask-image:radial-gradient(ellipse_58%_85%_at_50%_35%,#000_40%,transparent_100%)]"
            />
        </section>
    </div>
</template>
