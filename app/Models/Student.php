<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Student extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($student) {
            if (empty($student->pairing_code)) {
                $student->pairing_code = static::generateUniquePairingCode();
            }
        });
    }

    public static function generateUniquePairingCode(): string
    {
        do {
            $code = 'LNT-'.strtoupper(Str::random(6));
        } while (static::where('pairing_code', $code)->exists());

        return $code;
    }

    public function parent()
    {
        return $this->belongsTo(Parents::class, 'parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unitProgress()
    {
        return $this->hasMany(StudentUnitProgress::class);
    }

    public function chapterCompletions()
    {
        return $this->hasMany(StudentChapterCompletion::class);
    }

    public function streak()
    {
        return $this->hasOne(StudentStreak::class);
    }

    public function getActiveStreak(): int
    {
        return $this->streak?->active_streak ?? 0;
    }

    public function isStreakActiveToday(): bool
    {
        return $this->streak?->is_active_today ?? false;
    }
}
