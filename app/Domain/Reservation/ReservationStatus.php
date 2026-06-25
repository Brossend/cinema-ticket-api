<?php

declare(strict_types=1);

namespace App\Domain\Reservation;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
}
