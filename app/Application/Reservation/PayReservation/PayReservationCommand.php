<?php

declare(strict_types=1);

namespace App\Application\Reservation\PayReservation;

final readonly class PayReservationCommand
{
    public function __construct(
        public string $reservationId,
        public string $reservationToken,
        public string $customerName,
        public string $customerEmail,
    ) {}
}
