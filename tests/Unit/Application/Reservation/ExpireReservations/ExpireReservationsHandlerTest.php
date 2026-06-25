<?php

declare(strict_types=1);

use App\Application\Contracts\Clock;
use App\Application\Reservation\ExpireReservations\ExpireReservationsHandler;
use App\Application\Reservation\ReservationRepository;
use App\Domain\Reservation\Reservation;

final class ExpireReservationsFixedClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-06-25T12:00:00+00:00');
    }
}

final class ExpireReservationsInMemoryRepository implements ReservationRepository
{
    public ?DateTimeImmutable $expiredAt = null;

    public function findById(string $reservationId): ?Reservation
    {
        return null;
    }

    public function findByIdForUpdate(string $reservationId): ?Reservation
    {
        return null;
    }

    public function expirePendingForScreening(
        int $screeningId,
        DateTimeImmutable $now,
    ): void {}

    public function countSeatOccupyingByScreeningId(
        int $screeningId,
    ): int {
        return 0;
    }

    public function save(Reservation $reservation): void {}

    public function expireAllPending(
        DateTimeImmutable $now,
    ): int {
        $this->expiredAt = $now;

        return 3;
    }
}

it('expires all pending reservations up to current time', function (): void {
    $repository = new ExpireReservationsInMemoryRepository;

    $handler = new ExpireReservationsHandler(
        clock: new ExpireReservationsFixedClock,
        reservationRepository: $repository,
    );

    $expiredReservations = $handler->handle();

    expect($expiredReservations)->toBe(3)
        ->and($repository->expiredAt?->format('c'))
        ->toBe('2026-06-25T12:00:00+00:00');
});
