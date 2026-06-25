<?php

declare(strict_types=1);

namespace App\Domain\Screening\Exception;

use DomainException;

final class NoAvailableSeats extends DomainException {}
