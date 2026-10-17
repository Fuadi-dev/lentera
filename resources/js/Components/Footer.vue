<script setup>
import { Mail, MapPin, Phone } from 'lucide-vue-next';

const navigationLinks = [
    { label: 'Beranda', href: '#beranda' },
    { label: 'Tentang Kami', href: '#tentang' },
    { label: 'Fitur', href: '#fitur' },
];

const otherLinks = [
    { label: 'FAQ', href: '#faq' },
    { label: 'Panduan', href: '#panduan' },
];

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="text-gray-900 border-t bg-background">
        <div class="footer-content w-full max-w-6xl px-4 mx-auto py-14 sm:px-6 sm:py-16">
            <div class="grid gap-10 md:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
                <div class="max-w-xs">
                    <a href="#beranda" aria-label="Kembali ke Beranda Lentera">
                        <img
                            src="/images/brand/logo-lentera.png"
                            alt="Lentera"
                            class="w-auto h-10"
                        />
                    </a>
                    <p class="mt-5 text-sm leading-relaxed text-gray-600">
                        Teman belajar interaktif yang membantu setiap anak belajar dengan cara yang
                        menyenangkan dan bermakna.
                    </p>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Navigasi</h2>
                    <ul class="mt-5 space-y-3">
                        <li v-for="item in navigationLinks" :key="item.label">
                            <a
                                :href="item.href"
                                class="text-sm text-gray-600 transition-colors hover:text-gray-900"
                            >
                                {{ item.label }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Lainnya</h2>
                    <ul class="mt-5 space-y-3">
                        <li v-for="item in otherLinks" :key="item.label">
                            <a
                                :href="item.href"
                                class="text-sm text-gray-600 transition-colors hover:text-gray-900"
                            >
                                {{ item.label }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Kontak Kami</h2>
                    <ul class="mt-5 space-y-3">
                        <li class="flex items-center gap-3 text-sm text-gray-600">
                            <Phone class="shrink-0 size-4" aria-hidden="true" />
                            <a
                                href="https://wa.me/628989098432"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="underline hover:text-gray-900"
                            >
                                +62 898-9098-432
                            </a>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-gray-600">
                            <Mail class="shrink-0 size-4" aria-hidden="true" />
                            <a href="mailto:lentera.uny@gmail.com" class="hover:text-gray-900">
                                lentera.uny@gmail.com
                            </a>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600">
                            <MapPin class="shrink-0 mt-0.5 size-4" aria-hidden="true" />
                            <span>Yogyakarta</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="border-t-2 border-gray-200">
            <div class="w-full max-w-6xl px-4 pb-5 mx-auto text-center pt-7 sm:px-6">
                <p class="text-sm text-gray-500">
                    &copy; {{ currentYear }} <span class="text-primary">Lentera </span> All Rights
                    Reserved
                </p>
            </div>
        </div>
    </footer>
</template>
