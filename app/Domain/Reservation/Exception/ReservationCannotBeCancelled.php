<?php

declare(strict_types=1);

namespace App\Domain\Reservation\Exception;

use DomainException;

final class ReservationCannotBeCancelled extends DomainException {}
