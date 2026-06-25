<?php

declare(strict_types=1);

namespace App\Presentation\Http\Api\V1\Requests;

final class PayReservationRequest extends ReservationTokenRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:80',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:254',
            ],
            'reservationToken' => $this->reservationTokenRules(),
        ];
    }
}
