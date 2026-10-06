<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentUnitProgress extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'student_unit_progress';
    protected $guarded = [];
    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
