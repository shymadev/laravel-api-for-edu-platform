<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User\Profile;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    private const AVATAR_SRC_DIR = 'seeders/data/user_avatars';

    private const AVATAR_STORAGE_PREFIX = 'avatars/users/';

    public function run(): void
    {
        $this->command->info('Seeding users...');

        $users = $this->loadJson('users/users.json');

        if ($users === []) {
            $this->command->warn('users.json is empty or missing — skipping UserSeeder.');

            return;
        }

        $avatarUrlMap = $this->publishAvatarsToMinio();

        $created = 0;
        $skipped = 0;

        $bar = $this->command->getOutput()->createProgressBar(count($users));
        $bar->start();

        foreach ($users as $data) {
            if (User::query()->where('email', $data['email'])->exists()) {
                $skipped++;
                $bar->advance();

                continue;
            }

            try {
                $avatarUrl = null;
                if (isset($data['avatar']) && $data['avatar'] !== '') {
                    $avatarUrl = $avatarUrlMap->get($data['avatar']);
                }

                $profile = Profile::create([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'bio' => $data['bio'] ?? null,
                    'avatar_url' => $avatarUrl,
                ]);

                $roleId = match ($data['role']) {
                    'admin' => Role::ADMIN_ROLE_ID,
                    'moderator' => Role::MODERATOR_ROLE_ID,
                    default => Role::USER_ROLE_ID,
                };

                User::create([
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'password_hash' => Hash::make($data['password']),
                    'role_id' => $roleId,
                    'profile_id' => $profile->id,
                    'is_blocked' => false,
                ]);

                $created++;
            } catch (\Throwable $e) {
                Log::error('UserSeeder: failed to create user', [
                    'email' => $data['email'],
                    'error' => $e->getMessage(),
                ]);
                $this->command->newLine();
                $this->command->warn("Failed [{$data['email']}]: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info("Users seeded: {$created} created, {$skipped} skipped.");
    }

    /**
     * Upload user avatar JPGs to MinIO. Returns filename → public URL map.
     */
    private function publishAvatarsToMinio(): Collection
    {
        $srcDir = database_path(self::AVATAR_SRC_DIR);
        $urlMap = collect();

        if (!File::isDirectory($srcDir)) {
            $this->command->warn('No user_avatars directory found — avatars will be null.');

            return $urlMap;
        }

        foreach (File::files($srcDir) as $file) {
            $objectKey = self::AVATAR_STORAGE_PREFIX . $file->getFilename();

            try {
                if (!Storage::disk('minio')->exists($objectKey)) {
                    Storage::disk('minio')->put(
                        $objectKey,
                        File::get($file->getPathname()),
                        'public',
                    );
                }

                $urlMap->put($file->getFilename(), Storage::disk('minio')->url($objectKey));
            } catch (\Throwable $e) {
                $this->command->warn("Could not upload avatar {$file->getFilename()}: " . $e->getMessage());
            }
        }

        $this->command->info("Uploaded {$urlMap->count()} user avatars to MinIO.");

        return $urlMap;
    }

    /**
     * @param string $relativePath
     *
     * @return array<int, array<string, mixed>>
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
