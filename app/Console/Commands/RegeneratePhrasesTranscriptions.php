<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Education\Phrase;
use App\Services\EspokeTranscriptionService;
use Illuminate\Console\Command;

/**
 * Command to regenerate transcriptions for all phrases.
 */
class RegeneratePhrasesTranscriptions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'phrases:regenerate-transcriptions';

    /**
     * The console command description.
     */
    protected $description = 'Regenerate transcriptions for all phrases';

    /**
     * @param EspokeTranscriptionService $espokeTranscriptionService
     */
    public function __construct(
        protected readonly EspokeTranscriptionService $espokeTranscriptionService,
    ) {
        parent::__construct();
    }

    /**
     * Iterate over all phrases, regenerate their transcriptions, and report progress.
     *
     * @return void
     */
    public function handle(): void
    {
        $phrases = Phrase::all();
        $total = $phrases->count();
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();
        $processed = 0;

        foreach ($phrases as $phrase) {
            $phrase->transcription = $this->espokeTranscriptionService->transcribe($phrase->text);
            $phrase->save();
            $progressBar->advance();

            $processed++;
            $progressBar->setMessage('Processed ' . $processed . ' of ' . $total . ' phrases');
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('Transcriptions regenerated for ' . $total . ' phrases');
    }
}
