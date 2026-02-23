<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class DifficultyLevel extends Model
{
    protected $table = 'difficulty_levels';

    protected $fillable = [
        'name',
        'value',
        'description',
    ];

    public $timestamps = false;
}
