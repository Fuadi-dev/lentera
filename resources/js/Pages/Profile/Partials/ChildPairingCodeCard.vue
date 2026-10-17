<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { PhArrowsClockwise, PhCheck, PhCopy, PhSparkle, PhUsers } from '@phosphor-icons/vue';
import Button from '@/Components/ui/button/Button.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/Components/ui/alert-dialog';

const props = defineProps({
    pairingCode: {
        type: String,
        default: '',
    },
});

const isCopied = ref(false);
const isRegenerating = ref(false);

const copyCode = async () => {
    if (!props.pairingCode) return;
    try {
        await navigator.clipboard.writeText(props.pairingCode);
        isCopied.value = true;
        setTimeout(() => {
            isCopied.value = false;
        }, 2000);
    } catch {
        // Fallback if clipboard API is restricted
        const input = document.createElement('input');
        input.value = props.pairingCode;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        isCopied.value = true;
        setTimeout(() => {
            isCopied.value = false;
        }, 2000);
    }
};

const handleRegenerate = () => {
    isRegenerating.value = true;
    router.post(
        route('profile.pairing-code.regenerate'),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isRegenerating.value = false;
            },
        }
    );
};
</script>

<template>
    <section>
        <header class="flex items-center gap-3">
            <div>
                <h2 class="text-lg font-bold text-gray-900">
                    Kode Hubung Orang Tua
                </h2>
                <p class="text-sm text-gray-600">
                    Berikan kode ini kepada orang tua agar mereka dapat memantau aktivitas belajarmu.
                </p>
            </div>
        </header>

        <div class="mt-6 rounded-2xl border border-primary/20 bg-primary/5 p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="mt-2 flex items-center gap-3">
                        <span class="font-mono text-3xl font-black tracking-wider text-primary">
                            {{ pairingCode || 'Belum dibuat' }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Orang tua cukup memasukkan kode ini di dashboard mereka untuk menghubungkan akun.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Button
                        type="button"
                        variant="default"
                        class="gap-2 font-semibold shadow-sm"
                        @click="copyCode"
                    >
                        <PhCheck v-if="isCopied" :size="18" weight="bold" class="text-white" />
                        <PhCopy v-else :size="18" weight="bold" />
                        <span>{{ isCopied ? 'Tersalin!' : 'Salin Kode' }}</span>
                    </Button>

                    <AlertDialog>
                        <AlertDialogTrigger as-child>
                            <Button
                                type="button"
                                variant="outline"
                                class="gap-2 font-medium"
                                :disabled="isRegenerating"
                            >
                                <PhArrowsClockwise
                                    :size="18"
                                    weight="bold"
                                    :class="{ 'animate-spin': isRegenerating }"
                                    class="text-black"
                                />
                                <p class="text-black">{{ isRegenerating ? 'Membuat...' : 'Buat Kode Baru' }}</p>
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent class="border-0 ring-0 shadow-2xl outline-none focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0 bg-white">
                            <AlertDialogHeader>
                                <AlertDialogTitle class="text-lg font-bold">
                                    Buat Kode Hubung Baru?
                                </AlertDialogTitle>
                                <AlertDialogDescription class="text-sm text-muted-foreground">
                                    Apakah kamu yakin ingin membuat kode unik baru? Kode yang lama tidak akan bisa digunakan lagi oleh orang tua untuk menghubungkan akun.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel :disabled="isRegenerating">Batal</AlertDialogCancel>
                                <AlertDialogAction
                                    :disabled="isRegenerating"
                                    @click="handleRegenerate"
                                >
                                    {{ isRegenerating ? 'Memproses...' : 'Ya, Buat Kode Baru' }}
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </div>
        </div>
    </section>
</template>
