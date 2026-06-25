<?php

declare(strict_types=1);

namespace App\Application\Reservation;

use App\Domain\Reservation\Reservation;
use DateTimeImmutable;

interface ReservationRepository
{
    public function findById(string $reservationId): ?Reservation;

    public function findByIdForUpdate(string $reservationId): ?Reservation;

    public function expirePendingForScreening(
        int $screeningId,
        DateTimeImmutable $now,
    ): void;

    public function countSeatOccupyingByScreeningId(
        int $screeningId,
    ): int;

    public function save(Reservation $reservation): void;

    public function expireAllPending(
        DateTimeImmutable $now,
    ): int;
}
