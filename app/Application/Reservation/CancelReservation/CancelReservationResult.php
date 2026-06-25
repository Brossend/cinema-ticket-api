<?php

declare(strict_types=1);

namespace App\Application\Reservation\CancelReservation;

use App\Domain\Reservation\ReservationStatus;

final readonly class CancelReservationResult
{
    public function __construct(
        public string $id,
        public ReservationStatus $status,
    ) {}
}
