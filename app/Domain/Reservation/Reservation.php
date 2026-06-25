<?php

declare(strict_types=1);

namespace App\Domain\Reservation;

use App\Domain\Reservation\Exception\ReservationCannotBeCancelled;
use App\Domain\Reservation\Exception\ReservationCannotBePaid;
use App\Domain\Reservation\Exception\ReservationExpired;
use DateTimeImmutable;

final class Reservation
{
    private function __construct(
        public readonly string $id,
        public readonly int $screeningId,
        public readonly string $accessTokenHash,
        public readonly DateTimeImmutable $expiresAt,
        private ReservationStatus $status,
        private ?Customer $customer,
        private ?DateTimeImmutable $paidAt,
        private ?DateTimeImmutable $cancelledAt,
    ) {}

    public static function createPending(
        string $id,
        int $screeningId,
        string $accessTokenHash,
        DateTimeImmutable $expiresAt,
    ): self {
        return new self(
            id: $id,
            screeningId: $screeningId,
            accessTokenHash: $accessTokenHash,
            expiresAt: $expiresAt,
            status: ReservationStatus::Pending,
            customer: null,
            paidAt: null,
            cancelledAt: null,
        );
    }

    public static function reconstitute(
        string $id,
        int $screeningId,
        string $accessTokenHash,
        DateTimeImmutable $expiresAt,
        ReservationStatus $status,
        ?Customer $customer,
        ?DateTimeImmutable $paidAt,
        ?DateTimeImmutable $cancelledAt,
    ): self {
        return new self(
            id: $id,
            screeningId: $screeningId,
            accessTokenHash: $accessTokenHash,
            expiresAt: $expiresAt,
            status: $status,
            customer: $customer,
            paidAt: $paidAt,
            cancelledAt: $cancelledAt,
        );
    }

    public function status(): ReservationStatus
    {
        return $this->status;
    }

    public function customer(): ?Customer
    {
        return $this->customer;
    }

    public function paidAt(): ?DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function cancelledAt(): ?DateTimeImmutable
    {
        return $this->cancelledAt;
    }

    public function isPending(): bool
    {
        return $this->status === ReservationStatus::Pending;
    }

    public function isExpiredAt(DateTimeImmutable $now): bool
    {
        return $this->isPending() && $this->expiresAt <= $now;
    }

    public function occupiesSeatAt(DateTimeImmutable $now): bool
    {
        return $this->status === ReservationStatus::Paid
            || ($this->isPending() && $this->expiresAt > $now);
    }

    public function pay(Customer $customer, DateTimeImmutable $now): void
    {
        if ($this->isExpiredAt($now)) {
            throw new ReservationExpired;
        }

        if (! $this->isPending()) {
            throw new ReservationCannotBePaid;
        }

        $this->customer = $customer;
        $this->status = ReservationStatus::Paid;
        $this->paidAt = $now;
    }

    public function cancel(DateTimeImmutable $now): void
    {
        if ($this->isExpiredAt($now)) {
            throw new ReservationExpired;
        }

        if (! $this->isPending()) {
            throw new ReservationCannotBeCancelled;
        }

        $this->status = ReservationStatus::Cancelled;
        $this->cancelledAt = $now;
    }

    public function expire(DateTimeImmutable $now): bool
    {
        if (! $this->isExpiredAt($now)) {
            return false;
        }

        $this->status = ReservationStatus::Expired;

        return true;
    }
}
