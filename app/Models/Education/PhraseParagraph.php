<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class PhraseParagraph extends Model
{
    protected $table = 'phrase_paragraphs';

    protected $fillable = [
        'paragraph_id',
    ];

    public $timestamps = false;

    public function paragraph(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Paragraph::class, 'paragraph_id');
    }

    public function phrases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PhraseItem::class, 'phrase_paragraph_id');
    }
}
