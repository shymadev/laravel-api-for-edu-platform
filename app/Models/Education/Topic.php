<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    protected $table = 'topics';

    protected $fillable = [
        'course_id',
        'parent_id',
        'title',
        'is_active',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'parent_id');
    }

    public function subtopics(): HasMany
    {
        return $this->hasMany(Topic::class, 'parent_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'topic_id');
    }
}
