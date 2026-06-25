<?php

declare(strict_types=1);

namespace App\Application\Contracts;

interface ReservationTokenGenerator
{
    public function generate(): string;
}
