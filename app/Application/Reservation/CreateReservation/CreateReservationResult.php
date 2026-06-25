<?php

declare(strict_types=1);

namespace App\Application\Reservation\CreateReservation;

use DateTimeImmutable;

final readonly class CreateReservationResult
{
    public function __construct(
        public string $id,
        public string $reservationToken,
        public DateTimeImmutable $expiresAt,
    ) {}
}
