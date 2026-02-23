<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class Paragraph extends Model
{
    protected $table = 'paragraphs';

    protected $fillable = [
        'lesson_id',
        'order',
        'type',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function lesson(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function videoParagraph(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(VideoParagraph::class, 'paragraph_id');
    }

    public function textParagraph(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TextParagraph::class, 'paragraph_id');
    }

    public function phraseParagraph(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PhraseParagraph::class, 'paragraph_id');
    }

    public function testParagraph(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TestParagraph::class, 'paragraph_id');
    }
}
