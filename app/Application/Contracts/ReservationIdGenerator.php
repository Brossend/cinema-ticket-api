<?php

declare(strict_types=1);

namespace App\Application\Contracts;

interface ReservationIdGenerator
{
    public function generate(): string;
}
