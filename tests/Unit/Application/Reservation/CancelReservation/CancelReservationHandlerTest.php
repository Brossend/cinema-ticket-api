<?php

declare(strict_types=1);

use App\Application\Contracts\Clock;
use App\Application\Contracts\TransactionManager;
use App\Application\Reservation\CancelReservation\CancelReservationCommand;
use App\Application\Reservation\CancelReservation\CancelReservationHandler;
use App\Application\Reservation\Exception\ReservationAccessDenied;
use App\Application\Reservation\ReservationRepository;
use App\Application\Screening\ScreeningRepository;
use App\Domain\Reservation\Reservation;
use App\Domain\Reservation\ReservationStatus;
use App\Domain\Screening\Screening;

final class CancelReservationFixedClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-06-25T12:00:00+00:00');
    }
}

final class CancelReservationImmediateTransactionManager implements TransactionManager
{
    public function run(Closure $callback): mixed
    {
        return $callback();
    }
}

final class CancelReservationScreeningRepository implements ScreeningRepository
{
    public function findByIdForUpdate(int $screeningId): ?Screening
    {
        return new Screening(
            id: $screeningId,
            title: 'Интерстеллар',
            startsAt: new DateTimeImmutable('2026-06-26T19:00:00+00:00'),
            totalSeats: 10,
        );
    }
}

final class CancelReservationInMemoryRepository implements ReservationRepository
{
    public int $saveCount = 0;

    public function __construct(
        public ?Reservation $reservation,
    ) {}

    public function findById(string $reservationId): ?Reservation
    {
        return $this->reservation;
    }

    public function findByIdForUpdate(string $reservationId): ?Reservation
    {
        return $this->reservation;
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

    public function save(Reservation $reservation): void
    {
        $this->reservation = $reservation;
        $this->saveCount++;
    }

    public function expireAllPending(DateTimeImmutable $now): int
    {
        // TODO: Implement expireAllPending() method.
    }
}

function makeCancelReservationHandler(
    CancelReservationInMemoryRepository $reservationRepository,
): CancelReservationHandler {
    return new CancelReservationHandler(
        transactionManager: new CancelReservationImmediateTransactionManager,
        clock: new CancelReservationFixedClock,
        screeningRepository: new CancelReservationScreeningRepository,
        reservationRepository: $reservationRepository,
    );
}

it('cancels an active reservation with a valid token', function (): void {
    $reservation = Reservation::createPending(
        id: '56f96ec8-8bd9-41dc-9c34-a785dfdb9f6e',
        screeningId: 1,
        accessTokenHash: hash('sha256', 'secret-token'),
        expiresAt: new DateTimeImmutable('2026-06-25T12:02:00+00:00'),
    );

    $repository = new CancelReservationInMemoryRepository($reservation);

    $result = makeCancelReservationHandler($repository)->handle(
        new CancelReservationCommand(
            reservationId: $reservation->id,
            reservationToken: 'secret-token',
        ),
    );

    expect($result->status)->toBe(ReservationStatus::Cancelled)
        ->and($repository->saveCount)->toBe(1)
        ->and($repository->reservation?->cancelledAt())
        ->not->toBeNull();
});

it('marks an expired pending reservation as expired', function (): void {
    $reservation = Reservation::createPending(
        id: '56f96ec8-8bd9-41dc-9c34-a785dfdb9f6e',
        screeningId: 1,
        accessTokenHash: hash('sha256', 'secret-token'),
        expiresAt: new DateTimeImmutable('2026-06-25T11:59:00+00:00'),
    );

    $repository = new CancelReservationInMemoryRepository($reservation);

    $result = makeCancelReservationHandler($repository)->handle(
        new CancelReservationCommand(
            reservationId: $reservation->id,
            reservationToken: 'secret-token',
        ),
    );

    expect($result->status)->toBe(ReservationStatus::Expired)
        ->and($repository->saveCount)->toBe(1);
});

it('does not cancel a reservation with an invalid token', function (): void {
    $reservation = Reservation::createPending(
        id: '56f96ec8-8bd9-41dc-9c34-a785dfdb9f6e',
        screeningId: 1,
        accessTokenHash: hash('sha256', 'secret-token'),
        expiresAt: new DateTimeImmutable('2026-06-25T12:02:00+00:00'),
    );

    $repository = new CancelReservationInMemoryRepository($reservation);

    expect(
        fn () => makeCancelReservationHandler($repository)->handle(
            new CancelReservationCommand(
                reservationId: $reservation->id,
                reservationToken: 'wrong-token',
            ),
        ),
    )->toThrow(ReservationAccessDenied::class);

    expect($repository->saveCount)->toBe(0);
});
