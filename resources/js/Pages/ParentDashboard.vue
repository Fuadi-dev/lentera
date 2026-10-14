<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    PhBookOpen,
    PhCalendarCheck,
    PhCaretRight,
    PhFire,
    PhLinkBreak,
    PhPlus,
    PhSparkle,
    PhTrendUp,
    PhUserPlus,
    PhUsers,
} from '@phosphor-icons/vue';
import Card from '@/Components/ui/card/Card.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/Components/ui/collapsible';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/Components/ui/alert-dialog';

defineProps({
    children: {
        type: Array,
        default: () => [],
    },
});

const isDialogOpen = ref(false);
const childToUnpair = ref(null);
const isUnpairDialogOpen = ref(false);
const isUnpairing = ref(false);

const form = useForm({
    pairing_code: '',
});

const submitPairChild = () => {
    form.post(route('parent.children.pair'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            isDialogOpen.value = false;
        },
    });
};

const promptUnpair = (child) => {
    childToUnpair.value = child;
    isUnpairDialogOpen.value = true;
};

const confirmUnpair = () => {
    if (!childToUnpair.value) return;
    isUnpairing.value = true;
    router.delete(route('parent.children.unpair', childToUnpair.value.id), {
        preserveScroll: true,
        onFinish: () => {
            isUnpairing.value = false;
            isUnpairDialogOpen.value = false;
            childToUnpair.value = null;
        },
    });
};
</script>

<template>
    <Head title="Dashboard Orang Tua" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-foreground">Dashboard Orang Tua</h2>
                    <p class="text-xs text-muted-foreground sm:text-sm">
                        Pantau progres dan perkembangan belajar anak secara real-time
                    </p>
                </div>
                <Button
                    variant="default"
                    class="gap-2 font-semibold shadow-sm"
                    @click="isDialogOpen = true"
                >
                    <PhPlus :size="18" weight="bold" />
                    <span>Hubungkan Anak</span>
                </Button>
            </div>
        </template>

        <div class="px-4 pt-6 pb-12 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-[76rem] space-y-8">
                <!-- Banner Sambutan -->
                <section
                    class="relative flex items-center justify-between overflow-hidden border-2 bg-gray-50 sm:rounded-lg"
                >
                    <span
                        class="absolute right-32 top-5 select-none text-3xl twinkle md:right-56"
                        aria-hidden="true"
                        >⭐</span
                    >
                    <span
                        class="absolute bottom-6 right-24 select-none text-2xl twinkle md:right-44"
                        style="animation-delay: 0.8s"
                        aria-hidden="true"
                        >✨</span
                    >

                    <div class="p-6 md:p-10">
                        <h1 class="text-md font-bold text-primary md:text-4xl">
                            Halo {{ $page.props.auth.user.name }} 👋
                        </h1>
                        <p class="mt-2 text-sm font-medium text-gray-600 md:mt-3 md:text-base">
                            Pantau terus perkembangan belajar anakmu. Setiap dukungan kecil dari
                            orang tua berarti besar untuk perjalanan mereka!
                        </p>
                    </div>
                    <span
                        class="absolute inset-y-0 right-0 w-1/3 bg-gradient-to-l from-primary/10 to-transparent"
                        aria-hidden="true"
                    ></span>
                    <img
                        src="/images/brand/maskot.png"
                        alt="Maskot Lentera"
                        class="relative z-10 mr-6 h-full w-24 py-4 md:mr-12 md:w-36"
                    />
                </section>

                <!-- Daftar Anak yang Sudah Terhubung -->
                <div v-if="children.length" class="space-y-4">
                    <Collapsible
                        v-for="child in children"
                        :key="child.id || child.name"
                        as-child
                        :default-open="children.length <= 2 || children.indexOf(child) === 0"
                        :disabled="children.length <= 2"
                        class="group/child space-y-4"
                    >
                        <section :aria-labelledby="`progress-${child.name}`">
                            <CollapsibleTrigger as-child>
                                <div
                                    class="flex w-full cursor-pointer flex-wrap items-center justify-between gap-4 rounded-2xl border border-border bg-card px-5 py-4 text-left shadow-sm transition-colors hover:border-primary/40 data-[state=open]:rounded-b-lg md:px-6"
                                    :class="{ 'cursor-default hover:border-border': children.length <= 2 }"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary text-sm font-bold text-primary-foreground shadow-sm"
                                            aria-hidden="true"
                                        >
                                            {{ child.avatar }}
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs font-medium uppercase tracking-wide text-muted-foreground"
                                            >
                                                Progress anak
                                            </p>
                                            <h2
                                                :id="`progress-${child.name}`"
                                                class="mt-0.5 text-xl font-bold text-foreground"
                                            >
                                                {{ child.name }}
                                            </h2>
                                            <p class="text-xs text-muted-foreground">
                                                {{ child.class }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="rounded-full bg-primary/10 px-3 py-1.5 text-xs font-semibold text-primary"
                                        >
                                            Aktif belajar
                                        </div>

                                        <button
                                            type="button"
                                            title="Putuskan Hubungan"
                                            class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                            @click.stop="promptUnpair(child)"
                                        >
                                            <PhLinkBreak :size="18" weight="bold" />
                                            <span class="sr-only">Putuskan Hubungan</span>
                                        </button>

                                        <PhCaretRight
                                            v-if="children.length > 2"
                                            class="size-5 text-muted-foreground transition-transform group-data-[state=open]/child:rotate-90"
                                            aria-hidden="true"
                                        />
                                    </div>
                                </div>
                            </CollapsibleTrigger>

                            <CollapsibleContent class="space-y-4 pt-4">
                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                    <Card class="min-h-[158px] border border-border p-4 shadow-sm">
                                        <div class="flex items-center justify-between">
                                            <p
                                                class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >
                                                Progress keseluruhan
                                            </p>
                                            <span class="rounded-lg bg-primary/10 p-2 text-primary">
                                                <PhTrendUp :size="18" aria-hidden="true" />
                                            </span>
                                        </div>
                                        <p class="mt-4 text-3xl font-bold tracking-tight text-foreground">
                                            {{ child.overallProgress }}%
                                        </p>
                                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-primary/20">
                                            <div
                                                class="h-full rounded-full bg-primary"
                                                :style="{ width: `${child.overallProgress}%` }"
                                            ></div>
                                        </div>
                                    </Card>

                                    <Card class="min-h-[158px] border border-border p-4 shadow-sm">
                                        <div class="flex items-center justify-between">
                                            <p
                                                class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >
                                                Materi selesai
                                            </p>
                                            <span class="rounded-lg bg-primary/10 p-2 text-primary">
                                                <PhBookOpen :size="18" aria-hidden="true" />
                                            </span>
                                        </div>
                                        <p class="mt-4 text-3xl font-bold tracking-tight text-foreground">
                                            {{ child.completedLessons }}/{{ child.totalLessons }}
                                        </p>
                                        <p class="mt-2 text-xs text-muted-foreground">
                                            materi pembelajaran
                                        </p>
                                    </Card>

                                    <Card class="min-h-[158px] border border-border p-4 shadow-sm">
                                        <div class="flex items-center justify-between">
                                            <p
                                                class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >
                                                Streak belajar
                                            </p>
                                            <img
                                                :src="child.streakActiveToday ? '/images/ui/streak.png' : '/images/ui/not streak.png'"
                                                alt="Streak"
                                                class="object-contain w-7 h-7"
                                            />
                                        </div>
                                        <p class="mt-4 text-3xl font-bold tracking-tight text-foreground">
                                            {{ child.streak }} hari
                                        </p>
                                        <p
                                            class="mt-2 text-xs font-medium"
                                            :class="child.streakActiveToday ? 'text-orange-600' : 'text-muted-foreground'"
                                        >
                                            {{ child.streakActiveToday ? 'Api menyala hari ini 🔥' : (child.streak > 0 ? 'Belum belajar hari ini' : 'Belum ada streak') }}
                                        </p>
                                    </Card>

                                    <Card class="min-h-[158px] border border-border p-4 shadow-sm">
                                        <div class="flex items-center justify-between">
                                            <p
                                                class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                            >
                                                Aktivitas terakhir
                                            </p>
                                            <span class="rounded-lg bg-primary/10 p-2 text-primary">
                                                <PhCalendarCheck :size="18" aria-hidden="true" />
                                            </span>
                                        </div>
                                        <p
                                            class="mt-4 line-clamp-2 text-sm font-semibold leading-5 text-foreground"
                                        >
                                            {{ child.lastActivity }}
                                        </p>
                                        <p class="mt-2 text-xs text-muted-foreground">Baru saja</p>
                                    </Card>
                                </div>

                                <Card class="border border-border p-4 shadow-sm md:p-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <h3 class="text-base font-bold text-foreground md:text-lg">
                                                Progress per mata pelajaran
                                            </h3>
                                            <p class="mt-1 text-xs text-muted-foreground md:text-sm">
                                                Perkembangan belajar {{ child.name }} saat ini
                                            </p>
                                        </div>
                                        <span class="rounded-lg bg-muted p-2 text-muted-foreground">
                                            <PhBookOpen :size="20" aria-hidden="true" />
                                        </span>
                                    </div>
                                    <div class="mt-6 grid gap-x-8 gap-y-5 md:grid-cols-3">
                                        <div v-for="subject in child.subjects" :key="subject.name">
                                            <div class="mb-2 flex justify-between text-sm">
                                                <span class="font-medium text-foreground">{{
                                                    subject.name
                                                }}</span>
                                                <span class="font-semibold text-muted-foreground"
                                                    >{{ subject.progress }}%</span
                                                >
                                            </div>
                                            <div class="h-2.5 overflow-hidden rounded-full bg-muted">
                                                <div
                                                    class="h-full rounded-full transition-all duration-500"
                                                    :class="subject.color"
                                                    :style="{ width: `${subject.progress}%` }"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                </Card>
                            </CollapsibleContent>
                        </section>
                    </Collapsible>
                </div>

                <!-- Empty State jika Belum Ada Anak yang Terhubung -->
                <div
                    v-else
                    class="relative overflow-hidden rounded-2xl border-2 border-dashed border-primary/30 bg-card p-8 text-center shadow-sm sm:p-12"
                >
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary mb-4 shadow-sm">
                        <PhUsers :size="36" weight="bold" />
                    </div>

                    <h3 class="text-xl font-bold text-foreground sm:text-2xl">
                        Belum Ada Anak yang Terhubung
                    </h3>
                    <p class="mx-auto mt-2 max-w-lg text-sm text-muted-foreground sm:text-base">
                        Hubungkan akun anak Anda menggunakan kode unik pairing yang terdapat pada halaman profil akun anak untuk mulai memantau kemajuan belajarnya di sini.
                    </p>

                    <div class="mt-6 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <Button
                            size="lg"
                            class="gap-2 font-bold shadow-md"
                            @click="isDialogOpen = true"
                        >
                            <PhUserPlus :size="20" weight="bold" />
                            <span>Hubungkan Akun Anak Sekarang</span>
                        </Button>
                    </div>

                    <!-- Langkah Cara Menghubungkan -->
                    <div class="mt-10 border-t border-border/60 pt-8 text-left">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground text-center sm:text-left mb-4">
                            Cara Menghubungkan Akun Anak:
                        </h4>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="rounded-xl border border-border/80 bg-muted/30 p-4">
                                <div class="flex items-center gap-2 font-bold text-primary text-sm mb-1">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-white text-xs">1</span>
                                    <span>Buka Profil Anak</span>
                                </div>
                                <p class="text-xs text-muted-foreground leading-relaxed">
                                    Masuk ke akun anak Anda, lalu buka menu Profil.
                                </p>
                            </div>
                            <div class="rounded-xl border border-border/80 bg-muted/30 p-4">
                                <div class="flex items-center gap-2 font-bold text-primary text-sm mb-1">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-white text-xs">2</span>
                                    <span>Salin Kode Unik</span>
                                </div>
                                <p class="text-xs text-muted-foreground leading-relaxed">
                                    Temukan kartu "Kode Hubung Orang Tua" dan salin kode (contoh: LNT-XXXXXX).
                                </p>
                            </div>
                            <div class="rounded-xl border border-border/80 bg-muted/30 p-4">
                                <div class="flex items-center gap-2 font-bold text-primary text-sm mb-1">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-white text-xs">3</span>
                                    <span>Masukkan di Sini</span>
                                </div>
                                <p class="text-xs text-muted-foreground leading-relaxed">
                                    Klik tombol "Hubungkan Anak" dan tempel kode unik tersebut.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dialog Hubungkan Anak Menggunakan shadcn-vue dialog -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary sm:mx-0">
                        <PhUserPlus :size="24" weight="bold" />
                    </div>
                    <DialogTitle class="text-xl font-bold">Hubungkan Akun Anak</DialogTitle>
                    <DialogDescription class="text-sm text-muted-foreground">
                        Masukkan kode unik pairing yang tercantum di profil akun anak untuk mulai memantau perkembangannya.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitPairChild" class="space-y-4 py-2">
                    <div class="space-y-2">
                        <label for="pairing_code" class="text-xs font-bold uppercase tracking-wider text-muted-foreground">
                            Kode Unik Anak
                        </label>
                        <Input
                            id="pairing_code"
                            v-model="form.pairing_code"
                            type="text"
                            placeholder="Contoh: LNT-AIFXQB"
                            class="h-12 font-mono text-lg font-bold tracking-wider uppercase"
                            :disabled="form.processing"
                            @input="form.pairing_code = form.pairing_code.toUpperCase()"
                            autocomplete="off"
                            required
                        />
                        <p v-if="form.errors.pairing_code" class="text-xs font-semibold text-destructive">
                            {{ form.errors.pairing_code }}
                        </p>
                        <p v-else class="text-xs text-muted-foreground">
                            Kode unik terdiri dari awalan <span class="font-mono font-semibold text-foreground">LNT-</span> dan 6 karakter acak.
                        </p>
                    </div>

                    <DialogFooter class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-2 pt-2">
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="form.processing"
                            @click="isDialogOpen = false"
                        >
                            <p class="text-black">Batal</p>
                        </Button>
                        <Button
                            type="submit"
                            variant="default"
                            :disabled="form.processing || !form.pairing_code.trim()"
                            class="gap-2 font-semibold"
                        >
                            <span v-if="form.processing">Menghubungkan...</span>
                            <span v-else>Hubungkan Sekarang</span>
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Alert Dialog Putuskan Hubungan Akun Anak -->
        <AlertDialog v-model:open="isUnpairDialogOpen">
            <AlertDialogContent class="border-0 ring-0 shadow-2xl outline-none focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0">
                <AlertDialogHeader>
                    <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-2xl bg-destructive/10 text-destructive sm:mx-0">
                        <PhLinkBreak :size="24" weight="bold" />
                    </div>
                    <AlertDialogTitle class="text-xl font-bold">
                        Putuskan Hubungan Akun?
                    </AlertDialogTitle>
                    <AlertDialogDescription class="text-sm text-muted-foreground">
                        Apakah Anda yakin ingin memutuskan hubungan akun dengan <span class="font-semibold text-foreground">{{ childToUnpair?.name }}</span>? Progres belajar anak tidak akan dapat dipantau lagi dari dashboard Anda sampai dihubungkan kembali.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-2 pt-2">
                    <AlertDialogCancel :disabled="isUnpairing">
                        <p class="text-black">Batal</p>
                    </AlertDialogCancel>
                    <AlertDialogAction
                        class="gap-2 font-semibold"
                        :disabled="isUnpairing"
                        @click="confirmUnpair"
                    >
                        {{ isUnpairing ? 'Memutuskan...' : 'Ya, Putuskan Hubungan' }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AuthenticatedLayout>
</template>
