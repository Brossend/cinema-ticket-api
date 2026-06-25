<?php

declare(strict_types=1);

use App\Domain\Reservation\ReservationStatus;
use App\Infrastructure\Persistence\Eloquent\Models\ReservationModel;
use App\Infrastructure\Persistence\Eloquent\Models\ScreeningModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('expires only overdue pending reservations', function (): void {
    $screening = ScreeningModel::query()->create([
        'title' => 'Интерстеллар',
        'starts_at' => now()->addHour(),
        'total_seats' => 10,
    ]);

    $expiredReservation = ReservationModel::query()->create([
        'id' => (string) Str::uuid(),
        'screening_id' => $screening->id,
        'access_token_hash' => hash('sha256', 'expired-token'),
        'status' => ReservationStatus::Pending->value,
        'expires_at' => now()->subMinute(),
    ]);

    $activeReservation = ReservationModel::query()->create([
        'id' => (string) Str::uuid(),
        'screening_id' => $screening->id,
        'access_token_hash' => hash('sha256', 'active-token'),
        'status' => ReservationStatus::Pending->value,
        'expires_at' => now()->addMinute(),
    ]);

    $paidReservation = ReservationModel::query()->create([
        'id' => (string) Str::uuid(),
        'screening_id' => $screening->id,
        'access_token_hash' => hash('sha256', 'paid-token'),
        'status' => ReservationStatus::Paid->value,
        'expires_at' => now()->subMinute(),
        'paid_at' => now()->subMinutes(2),
    ]);

    $this->artisan('reservations:expire')
        ->expectsOutput('Expired reservations: 1')
        ->assertExitCode(0);

    $this->assertDatabaseHas('reservations', [
        'id' => $expiredReservation->id,
        'status' => ReservationStatus::Expired->value,
    ]);

    $this->assertDatabaseHas('reservations', [
        'id' => $activeReservation->id,
        'status' => ReservationStatus::Pending->value,
    ]);

    $this->assertDatabaseHas('reservations', [
        'id' => $paidReservation->id,
        'status' => ReservationStatus::Paid->value,
    ]);
});
