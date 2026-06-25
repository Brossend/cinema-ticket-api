<?php

declare(strict_types=1);

namespace App\Presentation\Http\Api\V1\Requests;

final class CancelReservationRequest extends ReservationTokenRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'reservationToken' => $this->reservationTokenRules(),
        ];
    }
}
