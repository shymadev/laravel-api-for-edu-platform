<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class TestOption extends Model
{
    protected $table = 'test_options';

    protected $fillable = [
        'question_id',
        'text',
        'is_correct',
    ];

    public $timestamps = false;

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function question(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TestQuestion::class, 'question_id');
    }
}
