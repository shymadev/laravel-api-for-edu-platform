<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User\User;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        Cache::tags(['statistics'])->flush();
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        Cache::tags(['statistics'])->flush();
    }
}
