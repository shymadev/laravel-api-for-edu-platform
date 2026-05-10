<?php

declare(strict_types=1);

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Ordered block within a lesson (video, text, test, etc.).
 */
class Paragraph extends Model
{
    public $timestamps = false;

    protected $table = 'paragraphs';

    protected $fillable = [
        'lesson_id',
        'order',
        'type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Lesson that owns this block.
     *
     * @return BelongsTo
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    /**
     * Video sub-block, if type is video.
     *
     * @return HasOne
     */
    public function videoParagraph(): HasOne
    {
        return $this->hasOne(VideoParagraph::class, 'paragraph_id');
    }

    /**
     * Text sub-block, if type is text.
     *
     * @return HasOne
     */
    public function textParagraph(): HasOne
    {
        return $this->hasOne(TextParagraph::class, 'paragraph_id');
    }

    /**
     * Phrase exercise sub-block, if type is phrases.
     *
     * @return HasOne
     */
    public function phraseParagraph(): HasOne
    {
        return $this->hasOne(PhraseParagraph::class, 'paragraph_id');
    }

    /**
     * Test sub-block, if type is test.
     *
     * @return HasOne
     */
    public function testParagraph(): HasOne
    {
        return $this->hasOne(TestParagraph::class, 'paragraph_id');
    }
}
