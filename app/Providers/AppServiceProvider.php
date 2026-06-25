<?php

namespace App\Providers;

use App\Application\Contracts\Clock;
use App\Application\Contracts\TransactionManager;
use App\Infrastructure\Time\LaravelClock;
use App\Infrastructure\Transactions\LaravelTransactionManager;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
