<?php

declare(strict_types=1);

use App\Domain\Reservation\Customer;
use App\Domain\Reservation\Exception\ReservationExpired;
use App\Domain\Reservation\Reservation;
use App\Domain\Reservation\ReservationStatus;

it('occupies a seat only until expiration time', function (): void {
    $reservation = Reservation::createPending(
        id: 'reservation-1',
        screeningId: 1,
        accessTokenHash: str_repeat('a', 64),
        expiresAt: new DateTimeImmutable('2026-06-25T12:02:00+00:00'),
    );

    expect($reservation->occupiesSeatAt(
        new DateTimeImmutable('2026-06-25T12:01:59+00:00'),
    ))->toBeTrue();

    expect($reservation->occupiesSeatAt(
        new DateTimeImmutable('2026-06-25T12:02:00+00:00'),
    ))->toBeFalse();
});

it('marks a pending reservation as paid', function (): void {
    $reservation = Reservation::createPending(
        id: 'reservation-1',
        screeningId: 1,
        accessTokenHash: str_repeat('a', 64),
        expiresAt: new DateTimeImmutable('2026-06-25T12:02:00+00:00'),
    );

    $reservation->pay(
        customer: new Customer('Иван', 'IVAN@example.com'),
        now: new DateTimeImmutable('2026-06-25T12:01:00+00:00'),
    );

    expect($reservation->status())->toBe(ReservationStatus::Paid)
        ->and($reservation->customer()?->email)->toBe('ivan@example.com')
        ->and($reservation->paidAt())->not->toBeNull();
});

it('does not allow payment after reservation expiration', function (): void {
    $reservation = Reservation::createPending(
        id: 'reservation-1',
        screeningId: 1,
        accessTokenHash: str_repeat('a', 64),
        expiresAt: new DateTimeImmutable('2026-06-25T12:02:00+00:00'),
    );

    expect(fn () => $reservation->pay(
        customer: new Customer('Иван', 'ivan@example.com'),
        now: new DateTimeImmutable('2026-06-25T12:02:00+00:00'),
    ))->toThrow(ReservationExpired::class);
});
