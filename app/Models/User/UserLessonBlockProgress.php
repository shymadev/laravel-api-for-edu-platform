<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Models\Education\Lesson;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-user progress for a block inside a lesson.
 *
 * @property int $block_index
 * @property bool $is_completed
 * @property array<string, mixed>|null $block_state
 */
class UserLessonBlockProgress extends Model
{
    public $timestamps = true;

    protected $table = 'user_lesson_block_progress';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'block_index',
        'block_type',
        'is_completed',
        'block_state',
    ];

    /**
     * User whose progress is recorded.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Lesson the block belongs to.
     *
     * @return BelongsTo
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'block_index' => 'integer',
            'is_completed' => 'boolean',
            'block_state' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
