<?php

declare(strict_types=1);

use App\Application\Contracts\Clock;
use App\Application\Contracts\ReservationIdGenerator;
use App\Application\Contracts\ReservationTokenGenerator;
use App\Application\Contracts\TransactionManager;
use App\Application\Reservation\CreateReservation\CreateReservationHandler;
use App\Application\Reservation\ReservationRepository;
use App\Application\Screening\ScreeningRepository;
use App\Domain\Reservation\Reservation;
use App\Domain\Reservation\ReservationStatus;
use App\Domain\Screening\Exception\NoAvailableSeats;
use App\Domain\Screening\Screening;

final class FixedClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-06-25T12:00:00+00:00');
    }
}

final class ImmediateTransactionManager implements TransactionManager
{
    public function run(Closure $callback): mixed
    {
        return $callback();
    }
}

final class InMemoryScreeningRepository implements ScreeningRepository
{
    public function __construct(
        private readonly ?Screening $screening,
    ) {}

    public function findByIdForUpdate(int $screeningId): ?Screening
    {
        return $this->screening;
    }
}

final class InMemoryReservationRepository implements ReservationRepository
{
    /**
     * @var list<Reservation>
     */
    public array $savedReservations = [];

    public function findById(string $reservationId): ?Reservation
    {
        return null;
    }

    public function findByIdForUpdate(string $reservationId): ?Reservation
    {
        return null;
    }

    public function __construct(
        private readonly int $occupiedSeats,
    ) {}

    public function expirePendingForScreening(
        int $screeningId,
        DateTimeImmutable $now,
    ): void {}

    public function countSeatOccupyingByScreeningId(
        int $screeningId,
    ): int {
        return $this->occupiedSeats;
    }

    public function save(Reservation $reservation): void
    {
        $this->savedReservations[] = $reservation;
    }
}

final class FixedReservationIdGenerator implements ReservationIdGenerator
{
    public function generate(): string
    {
        return '56f96ec8-8bd9-41dc-9c34-a785dfdb9f6e';
    }
}

final class FixedReservationTokenGenerator implements ReservationTokenGenerator
{
    public function generate(): string
    {
        return 'reservation-token';
    }
}

it('creates a pending reservation when a seat is available', function (): void {
    $reservationRepository = new InMemoryReservationRepository(9);

    $handler = new CreateReservationHandler(
        transactionManager: new ImmediateTransactionManager,
        clock: new FixedClock,
        screeningRepository: new InMemoryScreeningRepository(
            new Screening(
                id: 1,
                title: 'Интерстеллар',
                startsAt: new DateTimeImmutable('2026-06-26T19:00:00+00:00'),
                totalSeats: 10,
            ),
        ),
        reservationRepository: $reservationRepository,
        reservationIdGenerator: new FixedReservationIdGenerator,
        reservationTokenGenerator: new FixedReservationTokenGenerator,
    );

    $result = $handler->handle(1);

    expect($result->id)->toBe('56f96ec8-8bd9-41dc-9c34-a785dfdb9f6e')
        ->and($result->reservationToken)->toBe('reservation-token')
        ->and($result->expiresAt->format('c'))
        ->toBe('2026-06-25T12:02:00+00:00')
        ->and($reservationRepository->savedReservations)->toHaveCount(1)
        ->and($reservationRepository->savedReservations[0]->status())
        ->toBe(ReservationStatus::Pending);
});

it('does not create a reservation when all seats are occupied', function (): void {
    $reservationRepository = new InMemoryReservationRepository(10);

    $handler = new CreateReservationHandler(
        transactionManager: new ImmediateTransactionManager,
        clock: new FixedClock,
        screeningRepository: new InMemoryScreeningRepository(
            new Screening(
                id: 1,
                title: 'Интерстеллар',
                startsAt: new DateTimeImmutable('2026-06-26T19:00:00+00:00'),
                totalSeats: 10,
            ),
        ),
        reservationRepository: $reservationRepository,
        reservationIdGenerator: new FixedReservationIdGenerator,
        reservationTokenGenerator: new FixedReservationTokenGenerator,
    );

    expect(fn () => $handler->handle(1))
        ->toThrow(NoAvailableSeats::class);

    expect($reservationRepository->savedReservations)->toHaveCount(0);
});
