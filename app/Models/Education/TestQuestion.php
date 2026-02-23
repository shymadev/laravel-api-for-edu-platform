<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class TestQuestion extends Model
{
    protected $table = 'test_questions';

    protected $fillable = [
        'test_paragraph_id',
        'text',
    ];

    public $timestamps = false;

    public function testParagraph(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TestParagraph::class, 'test_paragraph_id');
    }

    public function options(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TestOption::class, 'question_id');
    }
}
