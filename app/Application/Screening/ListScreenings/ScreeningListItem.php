<?php

declare(strict_types=1);

namespace App\Application\Screening\ListScreenings;

use DateTimeImmutable;

final readonly class ScreeningListItem
{
    public function __construct(
        public int $id,
        public string $title,
        public DateTimeImmutable $startsAt,
        public int $totalSeats,
        public int $availableSeats,
    ) {}
}
