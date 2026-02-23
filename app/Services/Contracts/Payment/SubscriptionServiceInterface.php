<?php

declare(strict_types=1);

namespace App\Services\Contracts\Payment;

use App\DTO\Payment\SubscriptionStatusDTO;

interface SubscriptionServiceInterface
{
    /**
     * Create a Setup Intent for Stripe Elements.
     *
     * @param int $userId
     *
     * @return string
     */
    public function createSetupIntent(int $userId): string;

    /**
     * Create a new subscription for the user.
     *
     * @param int    $userId
     * @param string $paymentMethodId
     * @param bool   $withTrial
     *
     * @return array
     */
    public function subscribe(int $userId, string $paymentMethodId, bool $withTrial = true): array;

    /**
     * Cancel the user's subscription.
     *
     * @param int $userId
     *
     * @return void
     */
    public function cancel(int $userId): void;

    /**
     * Resume a cancelled subscription.
     *
     * @param int $userId
     *
     * @return void
     */
    public function resume(int $userId): void;

    /**
     * Get the user's subscription status.
     *
     * @param int $userId
     *
     * @return SubscriptionStatusDTO
     */
    public function status(int $userId): SubscriptionStatusDTO;

    /**
     * Check if user has premium subscription.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function isPremium(int $userId): bool;
}
