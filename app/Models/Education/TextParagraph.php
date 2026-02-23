<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class TextParagraph extends Model
{
    protected $table = 'text_paragraphs';

    protected $fillable = [
        'paragraph_id',
        'content',
    ];

    public $timestamps = false;

    public function paragraph(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Paragraph::class, 'paragraph_id');
    }
}
