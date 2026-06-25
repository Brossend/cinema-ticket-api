<?php

declare(strict_types=1);

namespace App\Presentation\Http\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PayReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
        ];
    }
}
