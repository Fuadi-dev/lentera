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
            'gap-2 font-semibold transition-all duration-300 rounded-xl px-4 py-2.5 shrink-0',
            completed
                ? 'bg-green-600 hover:bg-green-700 text-white border-green-600 shadow-sm'
                : 'text-slate-700 border-slate-200 hover:border-green-500 hover:text-green-600 hover:bg-green-50/50',
        ]"
        :disabled="completed || loading"
        @click="markAsDone"
    >
        <PhCheckCircle
            v-if="completed"
            :size="18"
            weight="fill"
            class="animate-[scale-in_300ms_ease-out] text-white shrink-0"
            aria-hidden="true"
        />
        <PhCircle
            v-else
            :size="18"
            class="shrink-0"
            aria-hidden="true"
        />
        <span>{{ completed ? 'Sudah selesai' : loading ? 'Menyimpan...' : 'Tandai selesai' }}</span>
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
