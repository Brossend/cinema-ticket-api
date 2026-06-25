<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Screening;

use App\Application\Screening\ScreeningRepository;
use App\Domain\Screening\Screening;
use App\Infrastructure\Persistence\Eloquent\Models\ScreeningModel;

final class EloquentScreeningRepository implements ScreeningRepository
{
    public function findByIdForUpdate(int $screeningId): ?Screening
    {
        $screening = ScreeningModel::query()
            ->lockForUpdate()
            ->find($screeningId);

        if ($screening === null) {
            return null;
        }

        return new Screening(
            id: (int) $screening->id,
            title: (string) $screening->title,
            startsAt: $screening->starts_at->toDateTimeImmutable(),
            totalSeats: (int) $screening->total_seats,
        );
    }
}
