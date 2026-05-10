<?php

declare(strict_types=1);

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent line in a phrase block (text + translation).
 */
class PhraseItem extends Model
{
    public $timestamps = false;

    protected $table = 'phrase_items';

    protected $fillable = [
        'phrase_paragraph_id',
        'text',
        'translation',
    ];

    /**
     * Phrase block this line belongs to.
     *
     * @return BelongsTo
     */
    public function phraseParagraph(): BelongsTo
    {
        return $this->belongsTo(PhraseParagraph::class, 'phrase_paragraph_id');
    }
}
