<?php

namespace App\Providers;

use App\Application\Contracts\Clock;
use App\Application\Contracts\ReservationIdGenerator;
use App\Application\Contracts\ReservationTokenGenerator;
use App\Application\Contracts\TransactionManager;
use App\Application\Reservation\ReservationRepository;
use App\Application\Screening\ListScreenings\ScreeningReadRepository;
use App\Application\Screening\ScreeningRepository;
use App\Infrastructure\Identifiers\LaravelReservationIdGenerator;
use App\Infrastructure\Persistence\Eloquent\Reservation\EloquentReservationRepository;
use App\Infrastructure\Persistence\Eloquent\Screening\EloquentScreeningReadRepository;
use App\Infrastructure\Persistence\Eloquent\Screening\EloquentScreeningRepository;
use App\Infrastructure\Security\SecureReservationTokenGenerator;
use App\Infrastructure\Time\LaravelClock;
use App\Infrastructure\Transactions\LaravelTransactionManager;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Clock::class, LaravelClock::class);

        $this->app->singleton(TransactionManager::class, LaravelTransactionManager::class);

        $this->app->bind(
            ScreeningReadRepository::class,
            EloquentScreeningReadRepository::class,
        );

        $this->app->bind(
            ScreeningRepository::class,
            EloquentScreeningRepository::class,
        );

        $this->app->bind(
            ReservationRepository::class,
            EloquentReservationRepository::class,
        );

        $this->app->singleton(
            ReservationIdGenerator::class,
            LaravelReservationIdGenerator::class,
        );

        $this->app->singleton(
            ReservationTokenGenerator::class,
            SecureReservationTokenGenerator::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
    }
}
