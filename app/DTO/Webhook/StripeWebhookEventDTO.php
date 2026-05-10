<?php

declare(strict_types=1);

namespace App\DTO\Webhook;

/**
 * Single normalized view of any Stripe webhook event JSON (`id`, `type`, `data.object`, …).
 */
final readonly class StripeWebhookEventDTO
{
    /**
     * @param string $id
     * @param string $type
     * @param array $payload
     */
    private function __construct(
        public string $id,
        public string $type,
        public array $payload,
    ) {
    }

    /**
     * Build a DTO from the raw webhook payload array.
     *
     * @param array $payload
     *
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            id: (string) ($payload['id'] ?? ''),
            type: (string) ($payload['type'] ?? ''),
            payload: $payload,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function dataObject(): array
    {
        $data = $this->payload['data']['object'] ?? null;

        return is_array($data) ? $data : [];
    }

    /**
     * Stripe API version string from the event payload, if present.
     *
     * @return string|null
     */
    public function apiVersion(): ?string
    {
        return isset($this->payload['api_version']) ? (string) $this->payload['api_version'] : null;
    }

    /**
     * Whether the event occurred in live mode (vs test mode).
     *
     * @return boolean
     */
    public function livemode(): bool
    {
        return (bool) ($this->payload['livemode'] ?? false);
    }

    /**
     * Stripe Customer id (`cus_…`) when it can be inferred from `data.object` for this event type.
     *
     * @return string|null
     */
    public function stripeCustomerId(): ?string
    {
        $object = $this->dataObject();

        if ($this->type !== '' && preg_match('/^customer\\.(?!subscription)/', $this->type) === 1) {
            $id = $object['id'] ?? null;

            return is_string($id) && str_starts_with($id, 'cus_') ? $id : null;
        }

        $customer = $object['customer'] ?? null;

        return is_string($customer) && str_starts_with($customer, 'cus_') ? $customer : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function subscriptionLogContext(): array
    {
        $o = $this->dataObject();

        return [
            'event_id' => $this->id,
            'type' => $this->type,
            'subscription_id' => $o['id'] ?? null,
            'customer_id' => $o['customer'] ?? null,
            'status' => $o['status'] ?? null,
            'cancel_at_period_end' => $o['cancel_at_period_end'] ?? null,
            'metadata' => is_array($o['metadata'] ?? null) ? $o['metadata'] : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function setupIntentLogContext(): array
    {
        $o = $this->dataObject();
        $lastError = $o['last_setup_error'] ?? null;
        $lastErrorMessage = is_array($lastError) ? ($lastError['message'] ?? null) : null;

        return [
            'event_id' => $this->id,
            'type' => $this->type,
            'setup_intent_id' => $o['id'] ?? null,
            'status' => $o['status'] ?? null,
            'customer_id' => $o['customer'] ?? null,
            'payment_method_id' => $o['payment_method'] ?? null,
            'cancellation_reason' => $o['cancellation_reason'] ?? null,
            'last_setup_error' => $lastErrorMessage,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function checkoutSessionLogContext(): array
    {
        $o = $this->dataObject();

        return [
            'event_id' => $this->id,
            'type' => $this->type,
            'session_id' => $o['id'] ?? null,
            'customer_id' => $o['customer'] ?? null,
            'customer_email' => $o['customer_details']['email'] ?? $o['customer_email'] ?? null,
            'client_reference_id' => $o['client_reference_id'] ?? null,
            'status' => $o['status'] ?? null,
            'payment_status' => $o['payment_status'] ?? null,
            'subscription_id' => $o['subscription'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function genericLogContext(): array
    {
        return [
            'event_id' => $this->id,
            'type' => $this->type,
            'object_id' => $this->dataObject()['id'] ?? null,
            'customer_id' => $this->stripeCustomerId(),
        ];
    }
}
