<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Contracts\ReservationTokenGenerator;

final class SecureReservationTokenGenerator implements ReservationTokenGenerator
{
    public function generate(): string
    {
        return bin2hex(random_bytes(32));
    }
}
