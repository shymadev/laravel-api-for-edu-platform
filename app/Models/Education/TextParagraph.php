<?php

declare(strict_types=1);

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent text content row for a text paragraph.
 */
class TextParagraph extends Model
{
    public $timestamps = false;

    protected $table = 'text_paragraphs';

    protected $fillable = [
        'paragraph_id',
        'content',
    ];

    /**
     * Parent paragraph row.
     *
     * @return BelongsTo
     */
    public function paragraph(): BelongsTo
    {
        return $this->belongsTo(Paragraph::class, 'paragraph_id');
    }
}
