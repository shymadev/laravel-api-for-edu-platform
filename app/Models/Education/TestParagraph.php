<?php

declare(strict_types=1);

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent test block under a lesson paragraph.
 */
class TestParagraph extends Model
{
    public $timestamps = false;

    protected $table = 'test_paragraphs';

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
     * Questions in this test block.
     *
     * @return HasMany
     */
    public function questions(): HasMany
    {
        return $this->hasMany(TestQuestion::class, 'test_paragraph_id');
    }
}
