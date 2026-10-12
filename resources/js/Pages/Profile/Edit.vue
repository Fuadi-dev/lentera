<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ChildPairingCodeCard from './Partials/ChildPairingCodeCard.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    pairingCode: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <Head title="Profil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-black">Profil</h2>
        </template>

        <div class="pt-6 pb-12">
            <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
                <!-- Pairing code for student/child -->
                <div
                    v-if="$page.props.auth.user.role === 'child'"
                    class="p-4 bg-white shadow sm:rounded-lg sm:p-8"
                >
                    <ChildPairingCodeCard :pairing-code="pairingCode" />
                </div>

                <div class="p-4 bg-white shadow sm:rounded-lg sm:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="p-4 bg-white shadow sm:rounded-lg sm:p-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <!-- <div class="p-4 bg-white shadow sm:rounded-lg sm:p-8"> -->
                <!-- not yet implemented -->
                <!-- <DeleteUserForm class="max-w-xl" /> -->
                <!-- </div> -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>
