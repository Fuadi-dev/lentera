<?php

namespace App\Models;

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
}
