<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Additional\Advertisement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Clears advertisement cache when ads are created, updated, or deleted.
 */
class AdvertisementObserver
{
    /**
     * @param Advertisement $advertisement
     *
     * @return void
     */
    public function created(Advertisement $advertisement): void
    {
        Cache::tags(['advertisements'])->flush();
        Log::channel('db')->info('Advertisement created', ['advertisement_id' => $advertisement->id]);
    }

    /**
     * @param Advertisement $advertisement
     *
     * @return void
     */
    public function updated(Advertisement $advertisement): void
    {
        Cache::tags(['advertisements'])->flush();
    }

    /**
     * @param Advertisement $advertisement
     *
     * @return void
     */
    public function deleted(Advertisement $advertisement): void
    {
        Cache::tags(['advertisements'])->flush();
        Log::channel('db')->info('Advertisement deleted', ['advertisement_id' => $advertisement->id]);
    }

    /**
     * @param Advertisement $advertisement
     *
     * @return void
     */
    public function restored(Advertisement $advertisement): void
    {
        Cache::tags(['advertisements'])->flush();
    }

    /**
     * @param Advertisement $advertisement
     *
     * @return void
     */
    public function forceDeleted(Advertisement $advertisement): void
    {
        Cache::tags(['advertisements'])->flush();
    }
}
