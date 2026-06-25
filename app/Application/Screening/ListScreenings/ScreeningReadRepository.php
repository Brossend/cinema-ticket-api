<?php

declare(strict_types=1);

namespace App\Application\Screening\ListScreenings;

use DateTimeImmutable;

interface ScreeningReadRepository
{
    /**
     * @return list<ScreeningListItem>
     */
    public function listUpcomingWithAvailability(
        DateTimeImmutable $now,
    ): array;
}
