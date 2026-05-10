<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User\User;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

/**
 * Syncs Stripe subscription objects into local `subscriptions` and `subscription_items` tables.
 */
class SyncStripeSubscriptions extends Command
{
    protected $signature = 'stripe:sync-subscriptions
                            {--limit=100 : Max subscriptions to fetch per page}
                            {--dry-run : Show what would be synced without writing}';

    protected $description = 'Sync Stripe subscriptions into local subscriptions / subscription_items tables';

    /**
     * Run the Stripe list + upsert loop with optional dry-run.
     *
     * @return integer
     */
    public function handle(): int
    {
        $secret = config('services.stripe.secret');

        if ($secret === null || $secret === '') {
            $this->error('STRIPE_SECRET is not set.');

            return self::FAILURE;
        }

        $productId = config('services.stripe.product_id');
        $isDryRun = $this->option('dry-run') === true;
        $limit = (int) $this->option('limit');

        if ($isDryRun) {
            $this->info('[dry-run] No changes will be written.');
        }

        $stripe = new StripeClient($secret);

        $this->info('Fetching subscriptions from Stripe...');

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        $subscriptions = $stripe->subscriptions->all([
            'limit' => $limit,
            'expand' => ['data.items'],
        ]);

        $bar = $this->output->createProgressBar();
        $bar->start();

        foreach ($subscriptions->autoPagingIterator() as $stripeSub) {
            $bar->advance();

            try {
                $user = User::where('stripe_id', $stripeSub->customer)->first();

                if ($user === null) {
                    $skipped++;

                    continue;
                }

                if ($productId !== null && $productId !== '') {
                    $hasProduct = false;
                    foreach ($stripeSub->items->data as $item) {
                        if (($item->price->product ?? null) === $productId) {
                            $hasProduct = true;
                            break;
                        }
                    }
                    if (!$hasProduct) {
                        $skipped++;

                        continue;
                    }
                }

                $existingSub = DB::table('subscriptions')
                    ->where('stripe_id', $stripeSub->id)
                    ->first();

                $subData = [
                    'user_id' => $user->id,
                    'type' => SubscriptionService::SUBSCRIPTION_NAME,
                    'stripe_id' => $stripeSub->id,
                    'stripe_status' => $stripeSub->status,
                    'stripe_price' => $stripeSub->items->data[0]->price->id ?? null,
                    'quantity' => $stripeSub->items->data[0]->quantity ?? 1,
                    'trial_ends_at' => $stripeSub->trial_end !== null
                        ? date('Y-m-d H:i:s', $stripeSub->trial_end)
                        : null,
                    'ends_at' => $stripeSub->cancel_at !== null
                        ? date('Y-m-d H:i:s', $stripeSub->cancel_at)
                        : ($stripeSub->canceled_at !== null ? date('Y-m-d H:i:s', $stripeSub->canceled_at) : null),
                    'updated_at' => now(),
                ];

                if ($isDryRun) {
                    $existingSub !== null ? $updated++ : $created++;

                    continue;
                }

                if ($existingSub !== null) {
                    DB::table('subscriptions')
                        ->where('stripe_id', $stripeSub->id)
                        ->update($subData);
                    $subscriptionId = $existingSub->id;
                    $updated++;
                } else {
                    $subData['created_at'] = now();
                    $subscriptionId = DB::table('subscriptions')->insertGetId($subData);
                    $created++;
                }

                foreach ($stripeSub->items->data as $item) {
                    $itemData = [
                        'subscription_id' => $subscriptionId,
                        'stripe_id' => $item->id,
                        'stripe_product' => $item->price->product ?? '',
                        'stripe_price' => $item->price->id,
                        'quantity' => $item->quantity ?? 1,
                        'updated_at' => now(),
                    ];

                    $existingItem = DB::table('subscription_items')
                        ->where('stripe_id', $item->id)
                        ->first();

                    if ($existingItem !== null) {
                        DB::table('subscription_items')
                            ->where('stripe_id', $item->id)
                            ->update($itemData);
                    } else {
                        $itemData['created_at'] = now();
                        DB::table('subscription_items')->insert($itemData);
                    }
                }
            } catch (\Throwable $e) {
                $errors++;
                Log::error('stripe:sync-subscriptions error', [
                    'stripe_sub_id' => $stripeSub->id ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
                $this->newLine();
                $this->warn('Error on ' . $stripeSub->id . ': ' . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine(2);

        $prefix = $isDryRun ? '[dry-run] ' : '';
        $this->info("{$prefix}Done. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}, Errors: {$errors}");

        return self::SUCCESS;
    }
}
