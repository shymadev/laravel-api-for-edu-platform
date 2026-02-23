<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Additional\WebhookEvent;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for handling Stripe webhooks.
 */
class StripeWebhookController extends CashierController
{
    /**
     * Handle the "customer.subscription.created" webhook event.
     *
     * @param array $payload the webhook payload
     *
     * @return Response
     */
    protected function handleCustomerSubscriptionCreated(array $payload): Response
    {
        $this->logWebhookEvent($payload);

        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;
        $subscriptionId = $payload['data']['object']['id'] ?? null;
        $status = $payload['data']['object']['status'] ?? null;

        if ($stripeCustomerId && $subscriptionId) {
            $user = User::where('stripe_id', $stripeCustomerId)->first();
            if ($user) {
                Log::info('Subscription created for user', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscriptionId,
                    'status' => $status,
                ]);
            }
        }

        return $this->successMethod();
    }

    /**
     * Handle the "customer.subscription.updated" webhook event.
     *
     * @param array $payload the webhook payload
     *
     * @return Response
     */
    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        $this->logWebhookEvent($payload);

        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;
        $subscriptionId = $payload['data']['object']['id'] ?? null;
        $status = $payload['data']['object']['status'] ?? null;

        if ($stripeCustomerId && $subscriptionId) {
            $user = User::where('stripe_id', $stripeCustomerId)->first();
            if ($user) {
                Log::info('Subscription updated for user', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscriptionId,
                    'status' => $status,
                ]);

                if ($status === 'active') {
                    Log::info('Subscription activated', ['user_id' => $user->id]);
                } elseif ($status === 'canceled') {
                    Log::info('Subscription canceled', ['user_id' => $user->id]);
                } elseif ($status === 'past_due') {
                    Log::warning('Subscription past due', ['user_id' => $user->id]);
                }
            }
        }

        return $this->successMethod();
    }

    /**
     * Handle the "customer.subscription.deleted" webhook event.
     *
     * @param array $payload the webhook payload
     *
     * @return Response
     */
    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $this->logWebhookEvent($payload);

        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;
        $subscriptionId = $payload['data']['object']['id'] ?? null;

        if ($stripeCustomerId && $subscriptionId) {
            $user = User::where('stripe_id', $stripeCustomerId)->first();
            if ($user) {
                Log::info('Subscription deleted for user', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscriptionId,
                ]);
            }
        }

        return $this->successMethod();
    }

    /**
     * Handle the "invoice.payment_succeeded" webhook event.
     *
     * @param array $payload the webhook payload
     *
     * @return Response
     */
    protected function handleInvoicePaymentSucceeded(array $payload): Response
    {
        $this->logWebhookEvent($payload);

        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;
        $amountPaid = $payload['data']['object']['amount_paid'] ?? 0;
        $invoiceId = $payload['data']['object']['id'] ?? null;

        if ($stripeCustomerId) {
            $user = User::where('stripe_id', $stripeCustomerId)->first();
            if ($user) {
                Log::info('Invoice payment succeeded', [
                    'user_id' => $user->id,
                    'invoice_id' => $invoiceId,
                    'amount' => $amountPaid / 100,
                ]);
            }
        }

        return $this->successMethod();
    }

    /**
     * Handle the "invoice.payment_failed" webhook event.
     *
     * @param array $payload the webhook payload
     *
     * @return Response
     */
    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        $this->logWebhookEvent($payload);

        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;
        $invoiceId = $payload['data']['object']['id'] ?? null;
        $attemptCount = $payload['data']['object']['attempt_count'] ?? 0;

        if ($stripeCustomerId) {
            $user = User::where('stripe_id', $stripeCustomerId)->first();
            if ($user) {
                Log::error('Invoice payment failed', [
                    'user_id' => $user->id,
                    'invoice_id' => $invoiceId,
                    'attempt_count' => $attemptCount,
                ]);
            }
        }

        return $this->successMethod();
    }

    /**
     * Handle the "payment_method.attached" webhook event.
     *
     * @param array $payload the webhook payload
     *
     * @return Response
     */
    protected function handlePaymentMethodAttached(array $payload): Response
    {
        $this->logWebhookEvent($payload);

        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;
        if ($stripeCustomerId) {
            $user = User::where('stripe_id', $stripeCustomerId)->first();
            if ($user) {
                Log::info('Payment method attached', ['user_id' => $user->id]);
            }
        }

        return $this->successMethod();
    }

    /**
     * Log a Stripe webhook event to the database.
     *
     * @param array $payload The webhook payload
     */
    private function logWebhookEvent(array $payload): void
    {
        try {
            $eventId = $payload['id'] ?? null;
            $eventType = $payload['type'] ?? null;

            if ($eventId && $eventType) {
                WebhookEvent::updateOrCreate(
                    ['stripe_event_id' => $eventId],
                    [
                        'type' => $eventType,
                        'payload' => json_encode($payload),
                        'processed' => true,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to log webhook event', [
                'error' => $e->getMessage(),
                'event_type' => $payload['type'] ?? 'unknown',
            ]);
        }
    }

    /**
     * Handle all incoming Stripe webhook requests.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $method = 'handle'.str_replace('.', '', ucwords($payload['type'], '.'));

        if (method_exists($this, $method)) {
            return $this->{$method}($payload);
        }

        Log::info('Unhandled webhook type', ['type' => $payload['type']]);

        return response()->json(['status' => 'success']);
    }
}
