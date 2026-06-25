<?php

declare(strict_types=1);

namespace App\Infrastructure\Time;

use App\Application\Contracts\Clock;
use DateTimeImmutable;

final class LaravelClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return now()->toImmutable();
    }
}
