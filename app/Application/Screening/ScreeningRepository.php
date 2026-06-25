<?php

declare(strict_types=1);

namespace App\Application\Screening;

use App\Domain\Screening\Screening;

interface ScreeningRepository
{
    public function findByIdForUpdate(int $screeningId): ?Screening;
}
