<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentStreak;
use Carbon\Carbon;

class StreakService
{
    /**
     * Record a learning activity for the student and update their streak.
     *
     * @return array{
     *     streak_increased: bool,
     *     current_streak: int,
     *     longest_streak: int,
     *     is_new_streak: bool
     * }
     */
    public function recordActivity(Student $student): array
    {
        /** @var StudentStreak $streak */
        $streak = StudentStreak::firstOrCreate(
            ['student_id' => $student->id],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_activity_date' => null,
            ]
        );

        $today = now()->startOfDay();
        $lastActivity = $streak->last_activity_date
            ? Carbon::parse($streak->last_activity_date)->startOfDay()
            : null;

        $streakIncreased = false;
        $isNewStreak = false;

        if ($lastActivity === null) {
            // First ever activity
            $streak->current_streak = 1;
            $streak->longest_streak = max($streak->longest_streak, 1);
            $streak->last_activity_date = $today;
            $streak->save();

            $streakIncreased = true;
            $isNewStreak = true;
        } elseif ($lastActivity->equalTo($today)) {
            // Already recorded activity today: do not increment again
            $streakIncreased = false;
        } elseif ($lastActivity->equalTo($today->copy()->subDay())) {
            // Consecutive day (yesterday was active)
            $streak->current_streak += 1;
            $streak->longest_streak = max($streak->longest_streak, $streak->current_streak);
            $streak->last_activity_date = $today;
            $streak->save();

            $streakIncreased = true;
        } else {
            // Streak broken (last activity was before yesterday): restart streak at 1
            $streak->current_streak = 1;
            $streak->longest_streak = max($streak->longest_streak, 1);
            $streak->last_activity_date = $today;
            $streak->save();

            $streakIncreased = true;
            $isNewStreak = true;
        }

        return [
            'streak_increased' => $streakIncreased,
            'current_streak' => (int) $streak->current_streak,
            'longest_streak' => (int) $streak->longest_streak,
            'is_new_streak' => $isNewStreak,
        ];
    }

    /**
     * Get active streak count for a student.
     * Returns 0 if last activity is older than yesterday.
     */
    public function getActiveStreak(Student $student): int
    {
        $streak = $student->streak;
        if (! $streak || ! $streak->last_activity_date) {
            return 0;
        }

        $today = now()->startOfDay();
        $lastActivity = Carbon::parse($streak->last_activity_date)->startOfDay();

        if ($lastActivity->greaterThanOrEqualTo($today->copy()->subDay())) {
            return (int) $streak->current_streak;
        }

        return 0;
    }
}
