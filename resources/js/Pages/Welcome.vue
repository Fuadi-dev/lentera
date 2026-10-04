<script setup>
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
            class="relative z-10 mx-auto flex w-full max-w-6xl shrink-0 items-center justify-between px-6 py-5"
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

            <Button
                v-if="$page.props.auth.user"
                :as="Link"
                :href="route('dashboard')"
                size="lg"
                class="rounded-xl px-5"
            >
                Dashboard
            </Button>
            <Button
                v-else
                :as="Link"
                :href="route('login')"
                size="lg"
                class="gap-2 rounded-xl px-5"
            >
                <span>Masuk</span>
                <LoginIcon class="size-5" />
            </Button>
        </header>

        <main class="relative z-10 mx-auto w-full max-w-4xl shrink-0 px-6 pt-6 text-center">
            <h1
                class="text-[clamp(2rem,7.5vh,3.75rem)] font-bold leading-tight text-primary"
            >
                Belajar Mandiri<br />Dengan Seru
            </h1>
            <p class="mx-auto mt-5 max-w-xl text-lg text-gray-700 md:text-xl">
                Dengan Lentera, anak mendapatkan pengalaman belajar yang lebih
                terarah
            </p>

            <div class="mt-8 flex items-center justify-center gap-3">
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

        <section class="relative mt-6 flex min-h-0 flex-1 justify-center overflow-hidden">
            <img
                src="/images/ui/hero.png"
                alt="Tampilan aplikasi Lentera di ponsel"
                class="relative z-10 h-full w-auto max-w-full object-contain px-4 [mask-image:radial-gradient(ellipse_60%_70%_at_50%_45%,#000_45%,transparent_100%)]"
            />
        </section>
    </div>
</template>
