<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User\User;
use App\Services\SubscriptionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Seeds fake Stripe subscriptions for users who have progress in premium courses.
 */
class PremiumSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding premium subscriptions...');

        $usersData = $this->loadJson('users/users.json');
        $premiumIndices = $this->loadJson('users/premium_subscriptions.json');

        if ($usersData === [] || $premiumIndices === []) {
            $this->command->warn('Required JSON files missing — skipping PremiumSubscriptionSeeder.');

            return;
        }

        $indexToEmail = [];
        foreach ($usersData as $idx => $data) {
            $indexToEmail[$idx] = $data['email'];
        }

        $emails = array_filter(
            array_map(fn (int $idx) => $indexToEmail[$idx] ?? null, $premiumIndices),
            fn (?string $v) => $v !== null,
        );

        $users = User::whereIn('email', $emails)->get()->keyBy('email');

        $created = 0;
        $skipped = 0;

        $bar = $this->command->getOutput()->createProgressBar(count($emails));
        $bar->start();

        foreach ($emails as $email) {
            $user = $users->get($email);

            if ($user === null) {
                $skipped++;
                $bar->advance();

                continue;
            }

            if (DB::table('subscriptions')
                ->where('user_id', $user->id)
                ->where('type', SubscriptionService::SUBSCRIPTION_NAME)
                ->exists()) {
                $skipped++;
                $bar->advance();

                continue;
            }

            try {
                $fakeStripeId = 'sub_SEED_' . Str::random(14);
                $fakeItemId = 'si_SEED_' . Str::random(14);
                $priceId = config('services.stripe.price_id');
                $productId = config('services.stripe.product_id');

                if ($user->stripe_id === null) {
                    $user->stripe_id = 'cus_SEED_' . Str::random(14);
                    $user->saveQuietly();
                }

                $subscriptionId = DB::table('subscriptions')->insertGetId([
                    'user_id' => $user->id,
                    'type' => SubscriptionService::SUBSCRIPTION_NAME,
                    'stripe_id' => $fakeStripeId,
                    'stripe_status' => 'active',
                    'stripe_price' => $priceId,
                    'quantity' => 1,
                    'trial_ends_at' => null,
                    'ends_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('subscription_items')->insert([
                    'subscription_id' => $subscriptionId,
                    'stripe_id' => $fakeItemId,
                    'stripe_product' => $productId,
                    'stripe_price' => $priceId,
                    'quantity' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $created++;
            } catch (\Throwable $e) {
                Log::error('PremiumSubscriptionSeeder: error', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
                $this->command->newLine();
                $this->command->warn("Failed [{$email}]: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info("Subscriptions seeded: {$created} created, {$skipped} skipped.");
    }

    /**
     * @param string $relativePath
     *
     * @return array<mixed>
     */
    private function loadJson(string $relativePath): array
    {
        $path = database_path('seeders/data/' . $relativePath);

        if (!File::exists($path)) {
            $this->command->warn("JSON file not found: {$path}");

            return [];
        }

        return json_decode(File::get($path), true) ?? [];
    }
}
