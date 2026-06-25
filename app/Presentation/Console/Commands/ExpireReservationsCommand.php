<?php

declare(strict_types=1);

namespace App\Presentation\Console\Commands;

use App\Application\Reservation\ExpireReservations\ExpireReservationsHandler;
use Illuminate\Console\Command;

final class ExpireReservationsCommand extends Command
{
    protected $signature = 'reservations:expire';

    protected $description = 'Mark expired pending reservations as expired';

    public function handle(
        ExpireReservationsHandler $expireReservationsHandler,
    ): int {
        $expiredReservations = $expireReservationsHandler->handle();

        $this->info("Expired reservations: {$expiredReservations}");

        return self::SUCCESS;
    }
}
