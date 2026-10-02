<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({
    name: String,
    email: String,
});

const form = useForm({
    role: 'child', // default
});

const submit = () => {
    form.post(route('auth.google.role.complete'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Selesaikan Pendaftaran" />

        <div class="mb-4 text-sm text-gray-600">
            Halo {{ name }}, hampir selesai! Silakan pilih peran (role) Anda di Lentera sebelum melanjutkan.
        </div>

        <form @submit.prevent="submit">
            <!-- Role Selection Segmented Control -->
            <div class="mt-4">
                <span class="block font-medium text-sm text-gray-700 mb-2">Pilih Peran Anda</span>
                <div class="flex space-x-2 bg-gray-100 p-1 rounded-lg">
                    <button
                        type="button"
                        @click="form.role = 'parent'"
                        :class="[
                            'flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors',
                            form.role === 'parent' 
                                ? 'bg-white text-gray-900 shadow-sm' 
                                : 'text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        👨‍👩‍👧 Orang Tua
                    </button>
                    <button
                        type="button"
                        @click="form.role = 'child'"
                        :class="[
                            'flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors',
                            form.role === 'child' 
                                ? 'bg-white text-gray-900 shadow-sm' 
                                : 'text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        👧 Anak
                    </button>
                </div>
                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <div class="mt-6">
                <PrimaryButton class="w-full justify-center" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Selesaikan Pendaftaran
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
