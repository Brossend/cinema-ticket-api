<?php

declare(strict_types=1);

namespace App\Infrastructure\Identifiers;

use App\Application\Contracts\ReservationIdGenerator;
use Illuminate\Support\Str;

final class LaravelReservationIdGenerator implements ReservationIdGenerator
{
    public function generate(): string
    {
        return (string) Str::uuid();
    }
}
