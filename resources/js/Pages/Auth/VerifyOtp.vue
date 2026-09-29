<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
});

const form = useForm({
    otp: '',
});

const submit = () => {
    form.post(route('auth.verify.otp'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi OTP" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold">Cek Email Kamu! 📩</h1>
            <p class="mt-2 font-normal text-gray-600 text-md">
                Kami telah mengirimkan 6 digit kode OTP ke <span class="font-semibold">{{ email }}</span>.
            </p>
        </div>
        
        <div v-if="$page.props.flash && $page.props.flash.success" class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">
            {{ $page.props.flash.success }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="otp" value="Kode OTP" />

                <TextInput
                    id="otp"
                    type="text"
                    class="block w-full mt-1 tracking-widest text-center"
                    v-model="form.otp"
                    required
                    autofocus
                    maxlength="6"
                    placeholder="XXXXXX"
                />

                <InputError class="mt-2" :message="form.errors.otp" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    class="w-full justify-center"
                >
                    Verifikasi & Selesai
                </PrimaryButton>
            </div>
            
            <div class="mt-4 text-center">
                <Link
                    :href="route('register')"
                    class="text-sm text-gray-600 underline hover:text-gray-900"
                >
                    Kembali atau Ganti Email
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
