<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User\User;
use Illuminate\Support\Facades\Cache;

/**
 * Flushes statistics cache when user records are created, updated, or removed.
 */
class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param User $user
     *
     * @return void
     */
    public function created(User $user): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the User "updated" event.
     *
     * @param User $user
     *
     * @return void
     */
    public function updated(User $user): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param User $user
     *
     * @return void
     */
    public function deleted(User $user): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the User "restored" event.
     *
     * @param User $user
     *
     * @return void
     */
    public function restored(User $user): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param User $user
     *
     * @return void
     */
    public function forceDeleted(User $user): void
    {
        Cache::tags(['statistics'])->flush();
    }
}
