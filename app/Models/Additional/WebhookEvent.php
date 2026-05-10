<?php

declare(strict_types=1);

namespace App\Models\Additional;

use Illuminate\Database\Eloquent\Model;

/**
 * Stored Stripe webhook event for idempotent processing.
 */
class WebhookEvent extends Model
{
    protected $table = 'webhook_events';

    protected $fillable = [
        'stripe_event_id',
        'type',
        'payload',
        'processed',
        'error',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed' => 'boolean',
        ];
    }
}
