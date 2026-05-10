<?php

declare(strict_types=1);

namespace App\Models\Additional;

use App\Observers\AdvertisementObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

/**
 * Promotional banner with optional schedule and link.
 */
#[ObservedBy(AdvertisementObserver::class)]
class Advertisement extends Model
{
    protected $table = 'advertisements';

    protected $fillable = [
        'image_url',
        'url',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Check if advertisement is permanent (no start/end dates).
     *
     * @return boolean
     */
    public function isPermanent(): bool
    {
        return is_null($this->starts_at) && is_null($this->ends_at);
    }

    /**
     * Check if advertisement is currently active.
     *
     * @return boolean
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->isPermanent()) {
            return true;
        }

        $now = now();

        if ($this->starts_at !== null && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at !== null && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }
}
