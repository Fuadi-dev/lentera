<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentChapterCompletion extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];
    protected $casts = [
        'is_passed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
