<?php

declare(strict_types=1);

namespace App\Services\Contracts\Statistics;

/**
 * Interface for providing statistics data.
 */
interface StatisticsProviderInterface
{
    public function getStatistics(): array;
}
