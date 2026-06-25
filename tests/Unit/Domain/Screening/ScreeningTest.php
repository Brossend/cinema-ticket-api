<?php

declare(strict_types=1);

use App\Domain\Screening\Exception\NoAvailableSeats;
use App\Domain\Screening\Screening;

it('allows reservation when at least one seat is free', function (): void {
    $screening = new Screening(
        id: 1,
        title: 'Интерстеллар',
        startsAt: new DateTimeImmutable('2026-06-27T14:00:00+00:00'),
        totalSeats: 10,
    );

    $screening->assertCanAcceptReservation(9);

    expect(true)->toBeTrue();
});

it('does not allow reservation when all seats are occupied', function (): void {
    $screening = new Screening(
        id: 1,
        title: 'Интерстеллар',
        startsAt: new DateTimeImmutable('2026-06-27T14:00:00+00:00'),
        totalSeats: 10,
    );

    expect(fn () => $screening->assertCanAcceptReservation(10))
        ->toThrow(NoAvailableSeats::class);
});
