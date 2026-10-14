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
}
