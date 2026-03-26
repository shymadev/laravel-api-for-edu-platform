<?php

declare(strict_types=1);

namespace App\DTO;

readonly class ToggleFavoriteDTO
{

    /**
     * Constructs a new ToggleFavoriteDTO instance.
     *
     * @param int $phraseId The ID of the phrase to toggle.
     * @param int $userId The ID of the user to toggle the favorite for.
     */
    public function __construct(
        public readonly int $phraseId,
        public readonly int $userId,
    ) {
    }

}
