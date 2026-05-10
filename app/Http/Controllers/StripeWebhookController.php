<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\StripeWebhookApplicationService;
use Illuminate\Http\Request;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for Stripe webhooks.
 */
class StripeWebhookController extends CashierWebhookController
{
    /**
     * Construct a new StripeWebhookController instance.
     *
     * @param \App\Services\StripeWebhookApplicationService $stripeWebhookApplicationService
     *
     * @return void
     */
    public function __construct(
        protected readonly StripeWebhookApplicationService $stripeWebhookApplicationService,
    ) {
        parent::__construct();
    }

    /**
     * Handle a Stripe webhook.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleWebhook(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);
        if (is_array($payload)) {
            $this->stripeWebhookApplicationService->processPayload($payload);
        }

        return parent::handleWebhook($request);
    }

    /**
     * Handle a Stripe checkout session completed webhook.
     *
     * @param array $payload
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCheckoutSessionCompleted(array $payload): Response
    {
        return $this->successMethod();
    }

    /**
     * Handle a Stripe customer subscription paused webhook.
     *
     * @param array $payload
     *
     * @return Response
     */
    protected function handleCustomerSubscriptionPaused(array $payload)
    {
        return $this->handleCustomerSubscriptionUpdated($payload);
    }

    /**
     * Handle a Stripe customer subscription resumed webhook.
     *
     * @param array $payload
     *
     * @return Response
     */
    protected function handleCustomerSubscriptionResumed(array $payload)
    {
        return $this->handleCustomerSubscriptionUpdated($payload);
    }

    /**
     * Handle a Stripe customer subscription trial will end webhook.
     *
     * @param array $payload
     *
     * @return Response
     */
    protected function handleCustomerSubscriptionTrialWillEnd(array $payload)
    {
        return $this->handleCustomerSubscriptionUpdated($payload);
    }
}
