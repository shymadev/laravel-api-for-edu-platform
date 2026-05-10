<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * Data Transfer Object for toggling the learned state of a favourite phrase.
 */
readonly class ToggleLearnedDTO
{
    /**
     * @param int $phraseId
     * @param int $userId
     */
    public function __construct(
        public int $phraseId,
        public int $userId,
    ) {
    }
}
