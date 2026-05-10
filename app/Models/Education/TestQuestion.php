<?php

declare(strict_types=1);

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent test question under a test paragraph.
 */
class TestQuestion extends Model
{
    public $timestamps = false;

    protected $table = 'test_questions';

    protected $fillable = [
        'test_paragraph_id',
        'text',
    ];

    /**
     * Test paragraph that owns this question.
     *
     * @return BelongsTo
     */
    public function testParagraph(): BelongsTo
    {
        return $this->belongsTo(TestParagraph::class, 'test_paragraph_id');
    }

    /**
     * Answer options for this question.
     *
     * @return HasMany
     */
    public function options(): HasMany
    {
        return $this->hasMany(TestOption::class, 'question_id');
    }
}
