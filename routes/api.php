<?php

use App\Presentation\Http\Api\V1\Controllers\CreateReservationController;
use App\Presentation\Http\Api\V1\Controllers\HealthController;
use App\Presentation\Http\Api\V1\Controllers\ListScreeningsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class)
        ->name('api.v1.health');

    Route::get('/screenings', ListScreeningsController::class)
        ->name('api.v1.screenings.index');

    Route::post(
        '/screenings/{screening}/reservations',
        CreateReservationController::class,
    )
        ->whereNumber('screening')
        ->name('api.v1.screenings.reservations.store');
});
