<?php

declare(strict_types=1);

use App\Domain\Reservation\ReservationStatus;
use App\Infrastructure\Persistence\Eloquent\Models\ReservationModel;
use App\Infrastructure\Persistence\Eloquent\Models\ScreeningModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('pays a reservation through the API', function (): void {
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

    $this->postJson(
        "/api/v1/reservations/{$reservation->id}/pay",
        [
            'name' => 'Иван Иванов',
            'email' => 'ivan@example.com',
        ],
        [
            'X-Reservation-Token' => $token,
        ],
    )
        ->assertOk()
        ->assertJsonPath('id', $reservation->id)
        ->assertJsonPath('status', ReservationStatus::Paid->value);

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => ReservationStatus::Paid->value,
        'customer_name' => 'Иван Иванов',
        'customer_email' => 'ivan@example.com',
    ]);
});

it('does not pay a reservation with an invalid token', function (): void {
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

    $this->postJson(
        "/api/v1/reservations/{$reservation->id}/pay",
        [
            'name' => 'Иван Иванов',
            'email' => 'ivan@example.com',
        ],
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
