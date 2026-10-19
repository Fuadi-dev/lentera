<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'child',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold">Yuk, Buat Akun Dulu!👋</h1>
            <p class="mt-2 font-normal text-gray-600 text-md">
                Daftar dan mulai perjalanan belajar yang menyenangkan.
            </p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="block w-full mt-1"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="block w-full mt-1"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="block w-full mt-1"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Konfirmasi Password" />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="block w-full mt-1"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-4">
                <InputLabel for="role" value="Mendaftar Sebagai" />

                <div class="flex p-1 mt-1 space-x-2 bg-gray-100 rounded-lg">
                    <button
                        type="button"
                        @click="form.role = 'child'"
                        :class="[
                            'flex-1 px-4 py-2 text-sm font-medium rounded-md transition-colors',
                            form.role === 'child'
                                ? 'bg-white text-gray-900 shadow-sm'
                                : 'text-gray-500 hover:text-gray-700',
                        ]"
                    >
                        👧 Anak
                    </button>
                    <button
                        type="button"
                        @click="form.role = 'parent'"
                        :class="[
                            'flex-1 px-4 py-2 text-sm font-medium rounded-md transition-colors',
                            form.role === 'parent'
                                ? 'bg-white text-gray-900 shadow-sm'
                                : 'text-gray-500 hover:text-gray-700',
                        ]"
                    >
                        👨‍👩‍👧 Orang Tua
                    </button>
                </div>

                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <div class="flex flex-col items-stretch mt-6 space-y-4">
                <PrimaryButton
                    class="justify-center w-full"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Daftar
                </PrimaryButton>

                <div class="relative flex items-center justify-center w-full my-4">
                    <div class="absolute w-full border-t border-gray-300"></div>
                    <span class="relative px-2 text-sm text-gray-500 bg-white">Atau</span>
                </div>

                <a
                    :href="route('auth.google')"
                    class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25"
                >
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                        <path
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                            fill="#4285F4"
                        />
                        <path
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                            fill="#34A853"
                        />
                        <path
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                            fill="#FBBC05"
                        />
                        <path
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                            fill="#EA4335"
                        />
                        <path d="M1 1h22v22H1z" fill="none" />
                    </svg>
                    Daftar dengan Google
                </a>

                <div class="text-center">
                    <p class="text-sm text-center text-gray-600">
                        Sudah punya akun?
                        <Link
                            :href="route('login')"
                            class="font-medium underline rounded-md text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >
                            Masuk
                        </Link>
                    </p>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
