<?php

use App\Presentation\Http\Api\V1\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class)
        ->name('api.v1.health');
});
