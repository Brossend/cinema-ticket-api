<?php

declare(strict_types=1);

namespace App\Application\Reservation\PayReservation;

use DateTimeImmutable;

final readonly class PayReservationResult
{
    public function __construct(
        public string $id,
        public string $status,
        public DateTimeImmutable $paidAt,
    ) {}
}
