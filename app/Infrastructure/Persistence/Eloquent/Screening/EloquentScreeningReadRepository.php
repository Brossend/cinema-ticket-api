<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Screening;

use App\Application\Screening\ListScreenings\ScreeningListItem;
use App\Application\Screening\ListScreenings\ScreeningReadRepository;
use App\Infrastructure\Persistence\Eloquent\Models\ScreeningModel;
use DateTimeImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

final class EloquentScreeningReadRepository implements ScreeningReadRepository
{
    /**
     * @return list<ScreeningListItem>
     */
    public function listUpcomingWithAvailability(
        DateTimeImmutable $now,
    ): array {
        $occupiedReservations = DB::table('reservations')
            ->select('screening_id')
            ->selectRaw('COUNT(*) AS occupied_seats')
            ->where(function (Builder $query) use ($now): void {
                $query
                    ->where('status', 'paid')
                    ->orWhere(function (Builder $query) use ($now): void {
                        $query
                            ->where('status', 'pending')
                            ->where('expires_at', '>', $now);
                    });
            })
            ->groupBy('screening_id');

        return ScreeningModel::query()
            ->leftJoinSub(
                $occupiedReservations,
                'occupied_reservations',
                static function (JoinClause $join): void {
                    $join->on(
                        'screenings.id',
                        '=',
                        'occupied_reservations.screening_id',
                    );
                },
            )
            ->where('screenings.starts_at', '>=', $now)
            ->orderBy('screenings.starts_at')
            ->select([
                'screenings.id',
                'screenings.title',
                'screenings.starts_at',
                'screenings.total_seats',
            ])
            ->selectRaw(
                'screenings.total_seats - '
                .'COALESCE(occupied_reservations.occupied_seats, 0) '
                .'AS available_seats',
            )
            ->get()
            ->map(
                static function (ScreeningModel $screening): ScreeningListItem {
                    return new ScreeningListItem(
                        id: (int) $screening->id,
                        title: (string) $screening->title,
                        startsAt: $screening->starts_at->toDateTimeImmutable(),
                        totalSeats: (int) $screening->total_seats,
                        availableSeats: (int) $screening->available_seats,
                    );
                },
            )
            ->all();
    }
}
