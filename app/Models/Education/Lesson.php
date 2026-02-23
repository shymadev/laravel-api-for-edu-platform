<?php

namespace App\Models\Education;

use App\Models\Education\Paragraphs\BaseParagraph;
use App\Models\User\UserCompletedLesson;
use App\Services\Education\ParagraphParser;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $table = 'lessons';

    protected $fillable = [
        'topic_id',
        'title',
        'weight',
        'content',
        'is_active',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
        'content' => 'array',
        'is_active' => 'boolean',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    public function completedBy(): HasMany
    {
        return $this->hasMany(UserCompletedLesson::class, 'lesson_id');
    }

    /**
     * Get parsed paragraphs from content.
     *
     * @return BaseParagraph[]
     */
    protected function paragraphs(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->content === null || $this->content === []) {
                    return [];
                }

                return ParagraphParser::parse($this->content);
            }
        );
    }
}
