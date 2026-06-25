<?php

declare(strict_types=1);

use App\Presentation\Console\Commands\ExpireReservationsCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ExpireReservationsCommand::class)
    ->everyMinute()
    ->withoutOverlapping(2)
    ->onOneServer();
