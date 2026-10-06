<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(Parents::class, 'parent_id');
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
}
