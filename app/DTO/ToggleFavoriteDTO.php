<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * Data Transfer Object for toggling a phrase favourite state for a user.
 */
readonly class ToggleFavoriteDTO
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
