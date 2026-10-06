<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function classes()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }
}
