<?php

declare(strict_types=1);

namespace App\Logging;

use App\Models\Additional\Log;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

/**
 * Custom Monolog handler to write log records to the database.
 */
class DatabaseLogProcessingHandler extends AbstractProcessingHandler
{
    /**
     * {@inheritdoc}
     */
    protected function write(LogRecord $record): void
    {
        Log::create([
            'channel' => $record->channel,
            'level' => $record->level->value,
            'message' => $record->message,
            'context' => $record->context,
            'user_id' => auth()->id() ?? 0,
            'ip' => request()->ip(),
            'url' => substr(request()->fullUrl(), 0, 128),
            'method' => request()->method(),
            'created_at' => now(),
        ]);
    }
}
