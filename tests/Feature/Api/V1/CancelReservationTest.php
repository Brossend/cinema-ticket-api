<?php

declare(strict_types=1);

use App\Domain\Reservation\ReservationStatus;
use App\Infrastructure\Persistence\Eloquent\Models\ReservationModel;
use App\Infrastructure\Persistence\Eloquent\Models\ScreeningModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('cancels a reservation through the API', function (): void {
    $screening = ScreeningModel::query()->create([
        'title' => 'Интерстеллар',
        'starts_at' => now()->addHour(),
        'total_seats' => 10,
    ]);

    $token = 'secret-token';

    $reservation = ReservationModel::query()->create([
        'id' => (string) Str::uuid(),
        'screening_id' => $screening->id,
        'access_token_hash' => hash('sha256', $token),
        'status' => ReservationStatus::Pending->value,
        'expires_at' => now()->addMinute(),
    ]);

    $this->deleteJson(
        "/api/v1/reservations/{$reservation->id}",
        [],
        [
            'X-Reservation-Token' => $token,
        ],
    )
        ->assertOk()
        ->assertJsonPath('id', $reservation->id)
        ->assertJsonPath('status', ReservationStatus::Cancelled->value);

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => ReservationStatus::Cancelled->value,
    ]);
});

it('does not cancel a reservation with an invalid token', function (): void {
    $screening = ScreeningModel::query()->create([
        'title' => 'Матрица',
        'starts_at' => now()->addHour(),
        'total_seats' => 10,
    ]);

    $reservation = ReservationModel::query()->create([
        'id' => (string) Str::uuid(),
        'screening_id' => $screening->id,
        'access_token_hash' => hash('sha256', 'correct-token'),
        'status' => ReservationStatus::Pending->value,
        'expires_at' => now()->addMinute(),
    ]);

    $this->deleteJson(
        "/api/v1/reservations/{$reservation->id}",
        [],
        [
            'X-Reservation-Token' => 'wrong-token',
        ],
    )
        ->assertForbidden()
        ->assertJsonPath(
            'message',
            'Недействительный токен бронирования.',
        );

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => ReservationStatus::Pending->value,
    ]);
});
