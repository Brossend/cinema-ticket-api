<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Reservation;

use App\Application\Reservation\ReservationRepository;
use App\Domain\Reservation\Reservation;
use App\Domain\Reservation\ReservationStatus;
use App\Infrastructure\Persistence\Eloquent\Models\ReservationModel;
use DateTimeImmutable;

final class EloquentReservationRepository implements ReservationRepository
{
    public function expirePendingForScreening(
        int $screeningId,
        DateTimeImmutable $now,
    ): void {
        ReservationModel::query()
            ->where('screening_id', $screeningId)
            ->where('status', ReservationStatus::Pending->value)
            ->where('expires_at', '<=', $now)
            ->update([
                'status' => ReservationStatus::Expired->value,
                'updated_at' => $now,
            ]);
    }

    public function countSeatOccupyingByScreeningId(
        int $screeningId,
    ): int {
        return ReservationModel::query()
            ->where('screening_id', $screeningId)
            ->whereIn('status', [
                ReservationStatus::Pending->value,
                ReservationStatus::Paid->value,
            ])
            ->count();
    }

    public function save(Reservation $reservation): void
    {
        $customer = $reservation->customer();

        ReservationModel::query()->updateOrCreate(
            [
                'id' => $reservation->id,
            ],
            [
                'screening_id' => $reservation->screeningId,
                'access_token_hash' => $reservation->accessTokenHash,
                'customer_name' => $customer?->name,
                'customer_email' => $customer?->email,
                'status' => $reservation->status()->value,
                'expires_at' => $reservation->expiresAt,
                'paid_at' => $reservation->paidAt(),
                'cancelled_at' => $reservation->cancelledAt(),
            ],
        );
    }
}
