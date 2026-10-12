<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from '@/Components/ui/button/Button.vue';
import { PhCheckCircle, PhCircle } from '@phosphor-icons/vue';

const props = defineProps({
    unitId: { type: [Number, String], required: true },
    completed: { type: Boolean, default: false },
});

const loading = ref(false);

function markAsDone() {
    if (props.completed || loading.value) {
        return;
    }

    loading.value = true;
    router.post(
        route('progress.units.complete', { unit: props.unitId }),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                loading.value = false;
            },
        }
    );
}
</script>

<template>
    <Button
        type="button"
        :variant="completed ? 'default' : 'outline'"
        :class="[
            'gap-2 transition-all duration-300',
            completed
                ? 'bg-green-600 hover:bg-green-700 text-white border-green-600'
                : '!text-black border-border hover:border-green-500 hover:!text-green-600',
        ]"
        :disabled="completed || loading"
        @click="markAsDone"
    >
        <PhCheckCircle
            v-if="completed"
            :size="18"
            weight="fill"
            class="animate-[scale-in_300ms_ease-out]"
            aria-hidden="true"
        />
        <PhCircle
            v-else
            :size="18"
            aria-hidden="true"
        />
        <p :class="completed ? '!text-white' : '!text-black'">{{ completed ? 'Sudah selesai' : loading ? 'Menyimpan...' : 'Tandai selesai' }}</p>
    </Button>
</template>

<style scoped>
@keyframes scale-in {
    from {
        transform: scale(0.5);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}
</style>
