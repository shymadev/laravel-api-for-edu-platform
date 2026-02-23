<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\DTO\Payment\SubscriptionStatusDTO;
use App\Models\User\User;
use App\Services\Contracts\Payment\SubscriptionServiceInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionService implements SubscriptionServiceInterface
{
    private const SUBSCRIPTION_NAME = 'premium';
    private const DEFAULT_PRICE_ID = 'price_1J2k3L4m5N6o7P8q9R0s1T2u';

    public function createSetupIntent(int $userId): string
    {
        $user = User::findOrFail($userId);

        try {
            return $user->createSetupIntent()->client_secret;
        } catch (\Exception $e) {
            Log::error('Failed to create setup intent', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to create setup intent: ' . $e->getMessage());
        }
    }

    /**
     * @throws IncompletePayment
     * @throws Exception
     */
    public function subscribe(int $userId, string $paymentMethodId, bool $withTrial = true): array
    {
        $user = User::findOrFail($userId);

        if ($user->subscribed(self::SUBSCRIPTION_NAME)) {
            throw new Exception("User is already subscribed.");
        }

        $priceId = config('services.stripe.price_id') ?? self::DEFAULT_PRICE_ID;

        try {
            $subscriptionBuilder = $user->newSubscription(self::SUBSCRIPTION_NAME, $priceId);

            if ($withTrial) {
                $subscriptionBuilder->trialDays(30);
            }

            $subscription = $subscriptionBuilder->create($paymentMethodId);

            Log::info('Subscription created successfully', [
                'user_id' => $userId,
                'subscription_id' => $subscription->stripe_id,
                'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
            ]);

            return [
                'subscription_id' => $subscription->id,
                'stripe_subscription_id' => $subscription->stripe_id,
                'status' => $subscription->stripe_status,
                'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
                'ends_at' => $subscription->ends_at?->toIso8601String(),
                'created_at' => $subscription->created_at->toIso8601String(),
            ];
        } catch (IncompletePayment $e) {
            Log::error('Incomplete payment during subscription', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to create subscription', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to create subscription: ' . $e->getMessage());
        }
    }

    public function cancel(int $userId): void
    {
        $user = User::findOrFail($userId);

        $subscription = $user->subscription(self::SUBSCRIPTION_NAME);

        if (! $subscription) {
            throw new Exception("User does not have an active subscription.");
        }

        try {
            $subscription->cancel();

            Log::info('Subscription cancelled', [
                'user_id' => $userId,
                'subscription_id' => $subscription->stripe_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to cancel subscription', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to cancel subscription: ' . $e->getMessage());
        }
    }

    public function resume(int $userId): void
    {
        $user = User::findOrFail($userId);

        $subscription = $user->subscription(self::SUBSCRIPTION_NAME);

        if (! $subscription) {
            throw new Exception("User does not have a subscription.");
        }

        if (! $subscription->onGracePeriod()) {
            throw new Exception("Subscription is not on grace period.");
        }

        try {
            $subscription->resume();

            Log::info('Subscription resumed', [
                'user_id' => $userId,
                'subscription_id' => $subscription->stripe_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to resume subscription', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Failed to resume subscription: ' . $e->getMessage());
        }
    }

    public function status(int $userId): SubscriptionStatusDTO
    {
        $user = User::findOrFail($userId);
        $subscription = $user->subscription(self::SUBSCRIPTION_NAME);

        $isPremium = $user->subscribed(self::SUBSCRIPTION_NAME);
        $onTrial = $subscription && $subscription->onTrial();

        return new SubscriptionStatusDTO(
            is_premium: $isPremium,
            ends_at: $subscription?->ends_at?->toIso8601String(),
            on_trial: $onTrial,
            trial_ends_at: $subscription?->trial_ends_at?->toIso8601String()
        );
    }

    public function isPremium(int $userId): bool
    {
        $user = User::findOrFail($userId);

        return $user->subscribed(self::SUBSCRIPTION_NAME);
    }
}
