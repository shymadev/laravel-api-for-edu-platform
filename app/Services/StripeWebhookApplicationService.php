<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Webhook\StripeWebhookEventDTO;
use App\Models\Additional\WebhookEvent;
use App\Models\User\User;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Facades\Log;

/**
 * Stripe webhook application service.
 */
#[Singleton]
final class StripeWebhookApplicationService
{
    /**
     * Process a Stripe webhook payload.
     *
     * @param array $payload
     *
     * @return void
     */
    public function processPayload(array $payload): void
    {
        $event = StripeWebhookEventDTO::fromPayload($payload);

        if ($event->id === '' || $event->type === '') {
            Log::warning('Stripe webhook payload missing id or type');

            return;
        }

        $existing = WebhookEvent::query()->where('stripe_event_id', $event->id)->first();
        if ($existing?->processed) {
            Log::debug('Stripe webhook already processed, skipping', [
                'event_id' => $event->id,
                'type' => $event->type,
            ]);

            return;
        }

        WebhookEvent::updateOrCreate(
            ['stripe_event_id' => $event->id],
            [
                'type' => $event->type,
                'payload' => $payload,
                'processed' => false,
                'error' => null,
            ],
        );

        try {
            $this->handleEvent($event);

            WebhookEvent::query()
                ->where('stripe_event_id', $event->id)
                ->update(['processed' => true, 'error' => null]);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook application handling failed', [
                'event_id' => $event->id,
                'type' => $event->type,
                'error' => $e->getMessage(),
            ]);

            WebhookEvent::query()
                ->where('stripe_event_id', $event->id)
                ->update(['processed' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Handle a Stripe webhook event.
     *
     * @param StripeWebhookEventDTO $event
     *
     * @return void
     */
    protected function handleEvent(StripeWebhookEventDTO $event): void
    {
        if (str_starts_with($event->type, 'customer.subscription.')) {
            $this->handleSubscriptionEvent($event);

            return;
        }

        if (str_starts_with($event->type, 'setup_intent.')) {
            $this->handleSetupIntentEvent($event);

            return;
        }

        if ($event->type === 'checkout.session.completed') {
            $this->handleCheckoutSessionCompleted($event);

            return;
        }

        if (str_starts_with($event->type, 'invoice.')) {
            $this->handleInvoiceEvent($event);

            return;
        }

        $this->handleOtherEvent($event);
    }

    /**
     * Handle a Stripe subscription webhook event.
     *
     * @param StripeWebhookEventDTO $event
     *
     * @return void
     */
    protected function handleSubscriptionEvent(StripeWebhookEventDTO $event): void
    {
        Log::info('Stripe subscription webhook', $event->subscriptionLogContext());
        $this->touchUserByStripeCustomer($event->stripeCustomerId());
    }

    /**
     * Handle a Stripe setup intent webhook event.
     *
     * @param StripeWebhookEventDTO $event
     *
     * @return void
     */
    protected function handleSetupIntentEvent(StripeWebhookEventDTO $event): void
    {
        $context = $event->setupIntentLogContext();

        if ($event->type === 'setup_intent.setup_failed') {
            Log::warning('Stripe setup_intent.setup_failed', $context);
        } else {
            Log::info('Stripe setup_intent webhook', $context);
        }

        $this->touchUserByStripeCustomer($event->stripeCustomerId());
    }

    /**
     * Handle a Stripe checkout session completed webhook event.
     *
     * @param StripeWebhookEventDTO $event
     *
     * @return void
     */
    protected function handleCheckoutSessionCompleted(StripeWebhookEventDTO $event): void
    {
        $context = $event->checkoutSessionLogContext();
        Log::info('Stripe checkout.session.completed', $context);

        $session = $event->dataObject();
        $stripeCustomerId = is_string($session['customer'] ?? null) ? $session['customer'] : null;
        $clientReferenceId = $session['client_reference_id'] ?? null;
        $customerEmail = $session['customer_details']['email'] ?? $session['customer_email'] ?? null;

        if ($stripeCustomerId === null) {
            return;
        }

        if ($clientReferenceId !== null) {
            $user = User::query()->where('id', (int) $clientReferenceId)->first();

            if ($user instanceof User) {
                $this->syncStripeCustomerId($user, $stripeCustomerId);

                return;
            }
        }

        if (is_string($customerEmail) && $customerEmail !== '') {
            $user = User::query()->where('email', $customerEmail)->first();

            if ($user instanceof User) {
                $this->syncStripeCustomerId($user, $stripeCustomerId);
            }
        }
    }

    /**
     * Handle a Stripe invoice webhook event.
     *
     * @param StripeWebhookEventDTO $event
     *
     * @return void
     */
    protected function handleInvoiceEvent(StripeWebhookEventDTO $event): void
    {
        $context = $event->genericLogContext();

        match ($event->type) {
            'invoice.payment_succeeded' => Log::info('Stripe invoice.payment_succeeded', $context),
            'invoice.payment_failed' => Log::warning('Stripe invoice.payment_failed', $context),
            default => Log::debug('Stripe invoice webhook', $context),
        };

        $this->touchUserByStripeCustomer($event->stripeCustomerId());
    }

    /**
     * Handle a Stripe other webhook event.
     *
     * @param StripeWebhookEventDTO $event
     *
     * @return void
     */
    protected function handleOtherEvent(StripeWebhookEventDTO $event): void
    {
        Log::debug('Stripe webhook', $event->genericLogContext());
        $this->touchUserByStripeCustomer($event->stripeCustomerId());
    }

    /**
     * Sync a Stripe customer id to a user.
     *
     * @param User $user
     * @param string $stripeCustomerId
     *
     * @return void
     */
    protected function syncStripeCustomerId(User $user, string $stripeCustomerId): void
    {
        if ($user->stripe_id === $stripeCustomerId) {
            return;
        }

        $user->stripe_id = $stripeCustomerId;
        $user->save();

        Log::info('Linked Stripe customer to user via checkout', [
            'user_id' => $user->id,
            'stripe_customer_id' => $stripeCustomerId,
        ]);
    }

    /**
     * Touch a user by Stripe customer id.
     *
     * @param ?string $stripeCustomerId
     *
     * @return void
     */
    protected function touchUserByStripeCustomer(?string $stripeCustomerId): void
    {
        if ($stripeCustomerId === null || $stripeCustomerId === '') {
            return;
        }

        $user = User::query()->where('stripe_id', $stripeCustomerId)->first();
        if ($user instanceof User) {
            Log::debug('Stripe webhook matched user by stripe_id', ['user_id' => $user->id]);
        }
    }
}
