<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Exceptions\IncompletePayment;

/**
 * Controller for managing user subscriptions.
 */
class SubscriptionController extends Controller
{
    /**
     * Construct a new SubscriptionController instance.
     *
     * @param \App\Services\SubscriptionService $subscriptionService
     *
     * @return void
     */
    public function __construct(protected readonly SubscriptionService $subscriptionService)
    {
    }

    /**
     * Create a setup intent for adding a payment method.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSetupIntent(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        try {
            $clientSecret = $this->subscriptionService->createSetupIntent($userId);

            return response()->json(['client_secret' => $clientSecret]);
        } catch (\Exception $e) {
            Log::error('Failed to create setup intent', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to create setup intent',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Subscribe the authenticated user to a premium plan.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method_id' => 'required|string',
            'with_trial' => 'sometimes|boolean',
        ]);

        try {
            $userId = $request->user()->id;
            $withTrial = $request->input('with_trial', true);

            $result = $this->subscriptionService->subscribe(
                $userId,
                $request->input('payment_method_id'),
                $withTrial,
            );

            return response()->json([
                'message' => 'Subscription created successfully',
                'data' => $result,
            ]);
        } catch (IncompletePayment $e) {
            return response()->json([
                'message' => 'Payment requires additional action',
                'payment_intent_client_secret' => $e->payment->client_secret,
            ], 402);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create subscription',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel the authenticated user's subscription.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $this->subscriptionService->cancel($userId);

            return response()->json(['message' => 'Subscription cancelled successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel subscription',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Resume a previously cancelled subscription.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resume(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $this->subscriptionService->resume($userId);

            return response()->json(['message' => 'Subscription resumed successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to resume subscription',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get the current subscription status for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $status = $this->subscriptionService->status($userId);

            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get subscription status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
