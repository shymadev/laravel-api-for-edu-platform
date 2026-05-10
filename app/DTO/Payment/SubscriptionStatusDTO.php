<?php

declare(strict_types=1);

namespace App\DTO\Payment;

/**
 * Data Transfer Object for the current subscription status of a user.
 */
readonly class SubscriptionStatusDTO
{
    /**
     * @param bool $is_premium
     * @param string|null $ends_at
     * @param bool $on_trial
     * @param string|null $trial_ends_at
     */
    public function __construct(
        public bool $is_premium,
        public ?string $ends_at,
        public bool $on_trial,
        public ?string $trial_ends_at,
    ) {
    }
}
