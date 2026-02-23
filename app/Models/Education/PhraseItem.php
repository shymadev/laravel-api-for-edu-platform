<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class PhraseItem extends Model
{
    protected $table = 'phrase_items';

    protected $fillable = [
        'phrase_paragraph_id',
        'text',
        'translation',
    ];

    public $timestamps = false;

    public function phraseParagraph(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PhraseParagraph::class, 'phrase_paragraph_id');
    }
}
