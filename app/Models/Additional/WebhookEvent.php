<?php

declare(strict_types=1);

namespace App\Models\Additional;

use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'processed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function markAsProcessed(): void
    {
        $this->update(['processed' => true]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'processed' => false,
            'error' => $error,
        ]);
    }
}
