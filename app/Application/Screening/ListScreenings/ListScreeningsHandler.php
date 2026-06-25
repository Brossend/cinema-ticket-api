<?php

declare(strict_types=1);

namespace App\Application\Screening\ListScreenings;

use App\Application\Contracts\Clock;

final readonly class ListScreeningsHandler
{
    public function __construct(
        private ScreeningReadRepository $screeningReadRepository,
        private Clock $clock,
    ) {}

    /**
     * @return list<ScreeningListItem>
     */
    public function handle(): array
    {
        return $this->screeningReadRepository
            ->listUpcomingWithAvailability($this->clock->now());
    }
}
