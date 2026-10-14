import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

/**
 * Mencatat aktivitas belajar harian (streak) dan menandai unit selesai dari
 * komponen latihan. Streak hanya bertambah jika hari ini belum ada aktivitas,
 * sehingga request dilewati bila sudah tercatat (server pun tetap idempotent).
 */
export function useStreakActivity() {
    const page = usePage();
    const recorded = ref(false);
    const unitMarkedDone = ref(false);

    const isChild = computed(() => page.props.auth?.user?.role === 'child');
    const isActiveToday = computed(() => page.props.auth?.streak_active_today === true);

    function recordStreakActivity() {
        if (recorded.value || isActiveToday.value) {
            return;
        }

        recorded.value = true;
        router.post(
            route('progress.streak.record'),
            {},
            { preserveScroll: true, preserveState: true }
        );
    }

    /**
     * Tandai unit selesai (sekaligus mencatat streak hari ini).
     * Jika unit sudah pernah selesai, hanya streak yang dicatat.
     */
    function markUnitDone(unitId, alreadyCompleted = false) {
        if (unitMarkedDone.value) {
            return;
        }

        unitMarkedDone.value = true;

        if (alreadyCompleted) {
            recordStreakActivity();

            return;
        }

        recorded.value = true;
        router.post(
            route('progress.units.complete', { unit: unitId }),
            {},
            { preserveScroll: true, preserveState: true }
        );
    }

    return { recordStreakActivity, markUnitDone, unitMarkedDone };
}
