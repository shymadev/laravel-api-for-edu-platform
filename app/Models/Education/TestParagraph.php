<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class TestParagraph extends Model
{
    protected $table = 'test_paragraphs';

    protected $fillable = [
        'paragraph_id',
    ];

    public $timestamps = false;

    public function paragraph(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Paragraph::class, 'paragraph_id');
    }

    public function questions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TestQuestion::class, 'test_paragraph_id');
    }
}
