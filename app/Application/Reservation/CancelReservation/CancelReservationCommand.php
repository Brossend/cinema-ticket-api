<?php

declare(strict_types=1);

namespace App\Application\Reservation\CancelReservation;

final readonly class CancelReservationCommand
{
    public function __construct(
        public string $reservationId,
        public string $reservationToken,
    ) {}
}
