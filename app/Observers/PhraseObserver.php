<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Education\Phrase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Invalidates phrase list and per-phrase cache when phrase records change.
 */
class PhraseObserver
{
    /**
     * @param Phrase $phrase
     *
     * @return void
     */
    public function created(Phrase $phrase): void
    {
        Cache::tags(['phrases'])->flush();
        Log::channel('db')->info('Phrase created', ['phrase_id' => $phrase->id, 'topic' => $phrase->topic]);
    }

    /**
     * @param Phrase $phrase
     *
     * @return void
     */
    public function updated(Phrase $phrase): void
    {
        Cache::tags(['phrases', "phrase.{$phrase->id}"])->flush();
    }

    /**
     * @param Phrase $phrase
     *
     * @return void
     */
    public function deleted(Phrase $phrase): void
    {
        Cache::tags(['phrases', "phrase.{$phrase->id}"])->flush();
        Log::channel('db')->info('Phrase deleted', ['phrase_id' => $phrase->id]);
    }

    /**
     * @param Phrase $phrase
     *
     * @return void
     */
    public function restored(Phrase $phrase): void
    {
        Cache::tags(['phrases'])->flush();
    }

    /**
     * @param Phrase $phrase
     *
     * @return void
     */
    public function forceDeleted(Phrase $phrase): void
    {
        Cache::tags(['phrases', "phrase.{$phrase->id}"])->flush();
    }
}
