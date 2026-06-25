<?php

declare(strict_types=1);

namespace App\Domain\Screening;

use App\Domain\Screening\Exception\NoAvailableSeats;
use DateTimeImmutable;
use InvalidArgumentException;

final class Screening
{
    public function __construct(
        public int $id,
        public string $title,
        public DateTimeImmutable $startsAt,
        public int $totalSeats,
    ) {
        if ($totalSeats <= 0) {
            throw new InvalidArgumentException('Количество мест должно быть больше нуля.');
        }
    }

    public function assertCanAcceptReservation(int $occupiedSeats): void
    {
        if ($occupiedSeats < 0) {
            throw new InvalidArgumentException('Количество занятых мест не может быть отрицательным.');
        }

        if ($occupiedSeats >= $this->totalSeats) {
            throw new NoAvailableSeats;
        }
    }
}
