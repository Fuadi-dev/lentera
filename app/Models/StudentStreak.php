<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentStreak extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'last_activity_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getActiveStreakAttribute(): int
    {
        if (! $this->last_activity_date) {
            return 0;
        }

        $lastActivity = Carbon::parse($this->last_activity_date)->startOfDay();
        $today = now()->startOfDay();

        if ($lastActivity->greaterThanOrEqualTo($today->copy()->subDay())) {
            return (int) $this->current_streak;
        }

        return 0;
    }

    public function getIsActiveTodayAttribute(): bool
    {
        if (! $this->last_activity_date) {
            return false;
        }

        return Carbon::parse($this->last_activity_date)->startOfDay()->equalTo(now()->startOfDay());
    }

    /**
     * Check if the streak is inactive today (flame off / not streak.png),
     * but NOT reset to 0 yet. The student still has today to practice and keep the streak alive.
     */
    public function getIsInactiveTodayAttribute(): bool
    {
        if (! $this->last_activity_date || (int) $this->current_streak <= 0) {
            return false;
        }

        $lastActivity = Carbon::parse($this->last_activity_date)->startOfDay();
        $today = now()->startOfDay();

        return $lastActivity->equalTo($today->copy()->subDay());
    }

    public function getIsBrokenAttribute(): bool
    {
        if (! $this->last_activity_date || (int) $this->current_streak <= 0) {
            return false;
        }

        $lastActivity = Carbon::parse($this->last_activity_date)->startOfDay();
        $today = now()->startOfDay();

        return $lastActivity->lessThan($today->copy()->subDay());
    }

    public function getBrokenStreakCountAttribute(): int
    {
        return $this->is_broken ? (int) $this->current_streak : 0;
    }
}
