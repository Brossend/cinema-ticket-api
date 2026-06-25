<?php

declare(strict_types=1);

namespace App\Presentation\Http\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class ReservationTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reservationToken' => $this->header(
                'X-Reservation-Token',
            ),
        ]);
    }

    /**
     * @return list<string>
     */
    protected function reservationTokenRules(): array
    {
        return [
            'required',
            'string',
            'regex:/\A[a-f0-9]{64}\z/',
        ];
    }

    public function reservationToken(): string
    {
        return (string) $this->input('reservationToken');
    }
}
