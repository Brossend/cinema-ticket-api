<?php

declare(strict_types=1);

use App\Application\Contracts\Clock;
use App\Application\Contracts\TransactionManager;
use App\Application\Reservation\Exception\ReservationAccessDenied;
use App\Application\Reservation\PayReservation\PayReservationCommand;
use App\Application\Reservation\PayReservation\PayReservationHandler;
use App\Application\Reservation\ReservationRepository;
use App\Application\Screening\ScreeningRepository;
use App\Domain\Reservation\Reservation;
use App\Domain\Reservation\ReservationStatus;
use App\Domain\Screening\Screening;

final class PayReservationFixedClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-06-25T12:01:00+00:00');
    }
}

final class PayReservationImmediateTransactionManager implements TransactionManager
{
    public function run(Closure $callback): mixed
    {
        return $callback();
    }
}

final class PayReservationScreeningRepository implements ScreeningRepository
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

final class PayReservationInMemoryRepository implements ReservationRepository
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
}

function makePayReservationHandler(
    PayReservationInMemoryRepository $reservationRepository,
): PayReservationHandler {
    return new PayReservationHandler(
        transactionManager: new PayReservationImmediateTransactionManager,
        clock: new PayReservationFixedClock,
        screeningRepository: new PayReservationScreeningRepository,
        reservationRepository: $reservationRepository,
    );
}

it('pays a pending reservation with a valid token', function (): void {
    $reservation = Reservation::createPending(
        id: '56f96ec8-8bd9-41dc-9c34-a785dfdb9f6e',
        screeningId: 1,
        accessTokenHash: hash('sha256', 'secret-token'),
        expiresAt: new DateTimeImmutable('2026-06-25T12:02:00+00:00'),
    );

    $repository = new PayReservationInMemoryRepository($reservation);

    $result = makePayReservationHandler($repository)->handle(
        new PayReservationCommand(
            reservationId: $reservation->id,
            reservationToken: 'secret-token',
            customerName: 'Иван Иванов',
            customerEmail: 'ivan@example.com',
        ),
    );

    expect($result->status)->toBe(ReservationStatus::Paid->value)
        ->and($repository->saveCount)->toBe(1)
        ->and($repository->reservation?->customer()?->email)
        ->toBe('ivan@example.com');
});

it('does not pay a reservation with an invalid token', function (): void {
    $reservation = Reservation::createPending(
        id: '56f96ec8-8bd9-41dc-9c34-a785dfdb9f6e',
        screeningId: 1,
        accessTokenHash: hash('sha256', 'secret-token'),
        expiresAt: new DateTimeImmutable('2026-06-25T12:02:00+00:00'),
    );

    $repository = new PayReservationInMemoryRepository($reservation);

    expect(
        fn () => makePayReservationHandler($repository)->handle(
            new PayReservationCommand(
                reservationId: $reservation->id,
                reservationToken: 'wrong-token',
                customerName: 'Иван Иванов',
                customerEmail: 'ivan@example.com',
            ),
        ),
    )->toThrow(ReservationAccessDenied::class);

    expect($repository->saveCount)->toBe(0);
});
