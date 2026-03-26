<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * Data Transfer Object for toggling the learned state of a favourite phrase.
 *
 * Carries the minimum identifiers required by the service layer to locate
 * the correct FavoritePhrase record and flip its `is_learned` flag.
 */
readonly class ToggleLearnedDTO
{
    /**
     * @param int $phraseId The ID of the phrase whose learned state should be toggled.
     * @param int $userId   The ID of the authenticated user who owns the favourite.
     */
    public function __construct(
        public readonly int $phraseId,
        public readonly int $userId,
    ) {
    }
}
