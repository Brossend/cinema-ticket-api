<?php

declare(strict_types=1);

namespace App\Application\Reservation\ExpireReservations;

use App\Application\Contracts\Clock;
use App\Application\Reservation\ReservationRepository;

final readonly class ExpireReservationsHandler
{
    public function __construct(
        private Clock $clock,
        private ReservationRepository $reservationRepository,
    ) {}

    public function handle(): int
    {
        return $this->reservationRepository->expireAllPending(
            $this->clock->now(),
        );
    }
}
