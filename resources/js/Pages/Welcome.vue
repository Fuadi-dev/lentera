<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Accessibility, BookOpen, Home, Sparkles, TrendingUp } from 'lucide-vue-next';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { animate, inView, stagger } from 'motion';
import Footer from '@/Components/Footer.vue';
import Faq from '@/Components/Faq.vue';
import Panduan from '@/Components/Panduan.vue';
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
const isScrolled = ref(false);
let stopInViews = [];

const handleScroll = () => {
    isScrolled.value = window.scrollY > 8;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const easeOut = [0.16, 1, 0.3, 1];

    // Initial state for scroll-triggered elements below the fold
    const initialHidden = document.querySelectorAll(
        '.tentang-text, .tentang-mascot, .fitur-header, .fitur-card, .panduan-header, .panduan-card, .faq-header, .faq-item, .footer-content'
    );
    initialHidden.forEach((el) => {
        el.style.opacity = '0';
    });

    // 1. Tentang Section: Triggers when the content grid itself enters view
    stopInViews.push(
        inView(
            '.tentang-grid',
            () => {
                animate('.tentang-text', { opacity: [0, 1], x: [-36, 0] }, { duration: 0.9, ease: easeOut });
                animate(
                    '.tentang-mascot',
                    { opacity: [0, 1], x: [36, 0], scale: [0.93, 1] },
                    { duration: 0.9, delay: 0.15, ease: easeOut }
                );
            },
            { margin: '0px 0px -100px 0px' }
        )
    );

    // 2. Fitur Section: Header and cards triggered independently
    stopInViews.push(
        inView(
            '.fitur-header',
            () => {
                animate('.fitur-header', { opacity: [0, 1], y: [24, 0] }, { duration: 0.75, ease: easeOut });
            },
            { margin: '0px 0px -80px 0px' }
        )
    );

    stopInViews.push(
        inView(
            '.fitur-grid',
            () => {
                const anim = animate(
                    '.fitur-card',
                    { opacity: [0, 1], y: [36, 0] },
                    {
                        delay: stagger(0.12, { startDelay: 0.05 }),
                        duration: 0.9,
                        ease: easeOut,
                    }
                );
                anim.finished.then(() => {
                    document.querySelectorAll('.fitur-card').forEach((el) => (el.style.transform = ''));
                });
            },
            { margin: '0px 0px -100px 0px' }
        )
    );

    // 3. Panduan Section: Header and cards triggered when actually in viewport
    stopInViews.push(
        inView(
            '.panduan-header',
            () => {
                animate('.panduan-header', { opacity: [0, 1], y: [24, 0] }, { duration: 0.75, ease: easeOut });
            },
            { margin: '0px 0px -80px 0px' }
        )
    );

    stopInViews.push(
        inView(
            '.panduan-grid',
            () => {
                const anim = animate(
                    '.panduan-card',
                    { opacity: [0, 1], y: [36, 0] },
                    {
                        delay: stagger(0.12, { startDelay: 0.05 }),
                        duration: 0.9,
                        ease: easeOut,
                    }
                );
                anim.finished.then(() => {
                    document.querySelectorAll('.panduan-card').forEach((el) => (el.style.transform = ''));
                });
            },
            { margin: '0px 0px -100px 0px' }
        )
    );

    // 4. FAQ Section: Header and accordion list
    stopInViews.push(
        inView(
            '.faq-header',
            () => {
                animate('.faq-header', { opacity: [0, 1], y: [24, 0] }, { duration: 0.75, ease: easeOut });
            },
            { margin: '0px 0px -80px 0px' }
        )
    );

    stopInViews.push(
        inView(
            '.faq-list',
            () => {
                animate(
                    '.faq-item',
                    { opacity: [0, 1], y: [24, 0] },
                    {
                        delay: stagger(0.08, { startDelay: 0.05 }),
                        duration: 0.8,
                        ease: easeOut,
                    }
                );
            },
            { margin: '0px 0px -100px 0px' }
        )
    );

    // 5. Footer: Triggered when reaching bottom
    stopInViews.push(
        inView(
            '.footer-content',
            () => {
                animate('.footer-content', { opacity: [0, 1], y: [24, 0] }, { duration: 0.85, ease: easeOut });
            },
            { margin: '0px 0px -60px 0px' }
        )
    );
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    stopInViews.forEach((stop) => {
        if (typeof stop === 'function') stop();
    });
    stopInViews = [];
});

const navLinks = [
    { label: 'Beranda', href: '#beranda' },
    { label: 'Tentang', href: '#tentang' },
    { label: 'Fitur', href: '#fitur' },
    { label: 'Panduan', href: '#panduan' },
    { label: 'FAQ', href: '#faq' },
];

const features = [
    {
        title: 'Belajar sesuai ritme anak',
        description:
            'Materi interaktif yang membantu anak memahami pelajaran dengan cara yang lebih menyenangkan.',
        icon: BookOpen,
        class: 'md:col-span-2',
    },
    {
        title: 'Pantau progres dengan mudah',
        description: 'Orang tua bisa melihat perkembangan belajar anak dari rumah.',
        icon: TrendingUp,
        class: '',
    },
    {
        title: 'Ramah untuk semua',
        description: 'Pengalaman belajar yang dirancang inklusif sejak awal.',
        icon: Accessibility,
        class: '',
    },
    {
        title: 'Materi yang dekat dengan anak',
        description: 'Beragam pelajaran untuk menemani setiap langkah kecil anak.',
        icon: Sparkles,
        class: 'md:col-span-2',
    },
];
</script>

<template>
    <Head title="Lentera - Belajar Mandiri Dengan Seru" />

    <div id="beranda" class="relative flex flex-col overflow-hidden bg-white h-dvh">
        <header
            :class="[
                'fixed inset-x-0 top-0 z-50 w-full border-b transition-all duration-300',
                isScrolled
                    ? 'border-border/50 bg-white/70 shadow-sm backdrop-blur-xl supports-[backdrop-filter]:bg-white/50'
                    : 'border-transparent bg-white',
            ]"
        >
            <div
                class="relative z-10 flex items-center justify-between w-full max-w-6xl px-4 py-4 mx-auto sm:px-6 sm:py-5"
            >
                <Link href="/" class="flex items-center">
                    <img src="/images/brand/logo-lentera.png" alt="Lentera" class="w-auto h-10" />
                </Link>

                <nav class="items-center hidden gap-8 md:flex">
                    <a
                        v-for="item in navLinks"
                        :key="item.label"
                        :href="item.href"
                        class="text-base font-medium text-gray-700 transition-colors hover:text-primary"
                    >
                        {{ item.label }}
                    </a>
                </nav>

                <div class="items-center hidden gap-2 sm:gap-3 md:flex">
                    <Button
                        v-if="$page.props.auth.user"
                        :as="Link"
                        :href="route('dashboard')"
                        size="lg"
                        class="px-4 rounded-xl sm:px-5"
                    >
                        Dashboard
                    </Button>
                    <Button
                        v-else
                        :as="Link"
                        :href="route('login')"
                        size="lg"
                        class="gap-2 px-4 rounded-xl sm:px-5"
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
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-7 w-7"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            v-if="!isMobileMenuOpen"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                        <path
                            v-else
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
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

            <div class="w-full px-6 mt-2">
                <Button
                    v-if="$page.props.auth.user"
                    :as="Link"
                    :href="route('dashboard')"
                    size="lg"
                    class="w-full px-4 rounded-xl"
                >
                    Dashboard
                </Button>
                <Button
                    v-else
                    :as="Link"
                    :href="route('login')"
                    size="lg"
                    class="flex items-center justify-center w-full gap-2 px-4 rounded-xl"
                >
                    <span>Masuk</span>
                    <LoginIcon class="size-5" />
                </Button>
            </div>
        </div>

        <main
            class="relative z-10 w-full max-w-4xl px-4 mx-auto text-center pt-28 shrink-0 sm:px-6 sm:pt-32"
        >
            <h1
                class="text-4xl font-bold leading-tight text-primary sm:text-5xl lg:text-[3.75rem] tracking-tighter"
            >
                Belajar Mandiri di Rumah<br />Dengan Seru
            </h1>
            <p class="max-w-xl mx-auto mt-6 text-lg text-gray-700 md:text-xl">
                Dengan Lentera, anak mendapatkan pengalaman belajar yang lebih terarah.
            </p>

            <div
                class="flex flex-col items-stretch justify-center gap-3 px-4 mt-6 mb-4 sm:flex-row sm:items-center sm:px-0"
            >
                <Button
                    as="a"
                    href="#fitur"
                    variant="outline"
                    size="lg"
                    class="px-6 rounded-xl text-foreground"
                >
                    Pelajari
                </Button>
                <Button
                    :as="Link"
                    :href="canRegister ? route('register') : route('login')"
                    size="lg"
                    class="gap-2 px-5 rounded-xl"
                >
                    <span>Coba Gratis</span>
                    <LoginIcon class="size-5" />
                </Button>
            </div>
        </main>

        <section class="relative flex justify-center flex-1 min-h-0 mt-1 overflow-hidden">
            <img
                src="/images/ui/hero.png"
                alt="Tampilan aplikasi Lentera di ponsel"
                class="relative z-10 h-full w-auto max-w-full origin-bottom scale-[2.5] object-contain object-bottom px-4 sm:scale-[1.4] [mask-image:radial-gradient(ellipse_58%_85%_at_50%_35%,#000_40%,transparent_100%)]"
            />
        </section>
    </div>

    <section id="tentang" class="py-20 bg-background sm:py-24">
        <div class="w-full max-w-6xl px-4 mx-auto sm:px-6">
            <div class="tentang-grid grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
                <div class="tentang-text">
                    <p class="text-sm font-semibold tracking-wide uppercase text-primary">
                        Tentang Lentera
                    </p>
                    <h2
                        class="mt-3 text-3xl font-bold leading-tight tracking-tight text-foreground sm:text-4xl"
                    >
                        Belajar nggak harus membosankan untuk bisa diakses semua anak
                    </h2>
                    <p class="max-w-xl mt-6 text-lg leading-relaxed text-muted-foreground">
                        Lentera lahir dari satu keresahan sederhana, masih banyak anak Sekolah Dasar
                        di Indonesia kesulitan menemukan cara belajar yang menyenangkan, sementara
                        anak dengan kebutuhan belajar khusus sering kali tidak punya pilihan sama
                        sekali. Kami percaya kedua masalah ini bisa diselesaikan sekaligus, dalam
                        satu platform yang sama.
                    </p>
                </div>

                <div class="tentang-mascot relative flex items-end justify-center min-h-80">
                    <div
                        class="absolute top-0 right-0 z-20 max-w-[15rem] rounded-2xl rounded-bl-sm bg-primary px-5 py-4 text-lg font-semibold text-primary-foreground shadow-lg sm:right-4 sm:translate-x-14"
                    >
                        Halo, siap belajar? 👋
                    </div>
                    <img
                        src="/images/brand/maskot.png"
                        alt="Maskot Lentera menyapa"
                        class="relative z-10 object-contain w-auto h-64 sm:h-80"
                    />
                </div>
            </div>
        </div>
    </section>

    <section id="fitur" class="py-20 bg-white sm:py-24">
        <div class="w-full max-w-6xl px-4 mx-auto sm:px-6">
            <div class="fitur-header max-w-2xl">
                <p class="text-sm font-semibold tracking-wide uppercase text-primary">
                    Fitur Lentera
                </p>
                <h2
                    class="mt-3 text-3xl font-bold leading-tight tracking-tight text-foreground sm:text-4xl"
                >
                    Semua yang anak butuhkan untuk belajar dengan seru
                </h2>
            </div>

            <div class="fitur-grid grid gap-5 mt-12 md:grid-cols-3 md:auto-rows-[15rem]">
                <Card
                    v-for="feature in features"
                    :key="feature.title"
                    :class="[
                        'fitur-card group relative min-h-[15rem] overflow-hidden rounded-2xl border border-border bg-muted/30 shadow-none transition-colors hover:border-primary/40',
                        feature.class,
                    ]"
                >
                    <CardContent class="relative z-10 flex flex-col h-full">
                        <div
                            class="flex items-center justify-center rounded-xl size-11 bg-primary/10"
                        >
                            <component
                                :is="feature.icon"
                                class="size-5 text-primary"
                                aria-hidden="true"
                            />
                        </div>
                        <div class="max-w-sm mt-auto">
                            <h3 class="text-xl font-semibold text-foreground">
                                {{ feature.title }}
                            </h3>
                            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                                {{ feature.description }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </section>

    <Panduan />

    <Faq />
    <Footer />
</template>
