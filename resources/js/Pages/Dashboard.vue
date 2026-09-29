<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Beranda" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-black">Beranda</h2>
        </template>

        <div class="pt-6 pb-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="relative flex items-center justify-between overflow-hidden border-2 bg-gray-50 sm:rounded-lg"
                >
                    <span
                        class="absolute text-3xl select-none right-32 top-5 md:right-56 twinkle"
                        aria-hidden="true"
                        >⭐</span
                    >
                    <span
                        class="absolute text-2xl select-none right-24 bottom-6 md:right-44 twinkle"
                        style="animation-delay: 0.8s"
                        aria-hidden="true"
                        >✨</span
                    >

                    <div class="p-6 md:p-10">
                        <h1 class="font-bold text-md md:text-4xl text-primary">
                            Halo {{ $page.props.auth.user.name }} 👋
                        </h1>
                        <p class="mt-2 text-sm font-medium text-gray-600 md:mt-3 md:text-base">
                            Yuk, semangat belajar hari ini! Setiap langkah kecil bikin kamu makin
                            pintar. Kamu pasti bisa!
                        </p>
                    </div>
                    <img src="/maskot.png" class="w-24 h-full py-4 mr-6 md:w-36 md:mr-12" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.twinkle {
    animation: twinkle 2.4s ease-in-out infinite;
}

@keyframes twinkle {
    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.5;
        transform: scale(0.85);
    }
}

@media (prefers-reduced-motion: reduce) {
    .twinkle {
        animation: none;
    }
}
</style>
