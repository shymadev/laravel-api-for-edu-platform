<?php

declare(strict_types=1);

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent link between a paragraph and ordered phrase items.
 */
class PhraseParagraph extends Model
{
    public $timestamps = false;

    protected $table = 'phrase_paragraphs';

    protected $fillable = [
        'paragraph_id',
    ];

    /**
     * Parent lesson paragraph.
     *
     * @return BelongsTo
     */
    public function paragraph(): BelongsTo
    {
        return $this->belongsTo(Paragraph::class, 'paragraph_id');
    }

    /**
     * Phrase lines for this block.
     *
     * @return HasMany
     */
    public function phrases(): HasMany
    {
        return $this->hasMany(PhraseItem::class, 'phrase_paragraph_id');
    }
}
