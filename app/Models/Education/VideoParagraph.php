<?php

declare(strict_types=1);

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent video URL row for a video paragraph.
 */
class VideoParagraph extends Model
{
    public $timestamps = false;

    protected $table = 'video_paragraphs';

    protected $fillable = [
        'paragraph_id',
        'url',
    ];

    /**
     * Parent paragraph row.
     *
     * @return BelongsTo
     */
    public function paragraph(): BelongsTo
    {
        return $this->belongsTo(Paragraph::class, 'paragraph_id');
    }
}
