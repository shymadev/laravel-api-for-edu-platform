<?php

namespace App\DTO\Payment;

class SubscriptionStatusDTO
{
    public function __construct(
        public bool $is_premium,
        public ?string $ends_at,
        public bool $on_trial,
        public ?string $trial_ends_at
    ) {
    }
}
