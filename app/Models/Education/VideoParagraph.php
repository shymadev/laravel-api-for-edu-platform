<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class VideoParagraph extends Model
{
    protected $table = 'video_paragraphs';

    protected $fillable = [
        'paragraph_id',
        'url',
    ];

    public $timestamps = false;

    public function paragraph(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Paragraph::class, 'paragraph_id');
    }
}
