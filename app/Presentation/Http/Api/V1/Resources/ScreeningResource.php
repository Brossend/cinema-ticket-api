<?php

declare(strict_types=1);

namespace App\Presentation\Http\Api\V1\Resources;

use App\Application\Screening\ListScreenings\ScreeningListItem;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ScreeningResource extends JsonResource
{
    /**
     * @return array<string, int|string>
     */
    public function toArray(Request $request): array
    {
        /** @var ScreeningListItem $screening */
        $screening = $this->resource;

        return [
            'id' => $screening->id,
            'title' => $screening->title,
            'startsAt' => $screening->startsAt
                ->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d\TH:i:s\Z'),
            'totalSeats' => $screening->totalSeats,
            'availableSeats' => $screening->availableSeats,
        ];
    }
}
