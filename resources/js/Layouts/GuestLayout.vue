<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen bg-secondary">
        <div class="hidden lg:block lg:w-1/2">
            <img
                class="object-cover w-full h-full px-2 py-2 rounded-3xl"
                src="/images/ui/teacher.jpg"
                alt="Ilustrasi Belajar"
            />
        </div>

        <div
            class="flex flex-col items-center justify-center w-full px-6 pt-6 sm:px-0 sm:pt-0 lg:w-1/2"
        >
            <div class="block pb-1">
                <Link href="/">
                    <ApplicationLogo class="w-20 h-12 text-gray-500 fill-current" />
                </Link>
            </div>

            <div
                class="w-full px-6 pb-8 mt-6 overflow-hidden bg-transparent sm:max-w-md sm:rounded-lg"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
