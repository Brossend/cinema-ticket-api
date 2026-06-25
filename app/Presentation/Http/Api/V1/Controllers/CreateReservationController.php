<?php

declare(strict_types=1);

namespace App\Presentation\Http\Api\V1\Controllers;

use App\Application\Reservation\CreateReservation\CreateReservationHandler;
use App\Application\Screening\Exception\ScreeningNotFound;
use App\Domain\Screening\Exception\NoAvailableSeats;
use App\Domain\Screening\Exception\ScreeningUnavailable;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class CreateReservationController
{
    public function __construct(
        private CreateReservationHandler $createReservationHandler,
    ) {}

    public function __invoke(int $screening): JsonResponse
    {
        try {
            $reservation = $this->createReservationHandler
                ->handle($screening);

            return response()->json([
                'id' => $reservation->id,
                'reservationToken' => $reservation->reservationToken,
                'expiresAt' => $reservation->expiresAt
                    ->setTimezone(new DateTimeZone('UTC'))
                    ->format('Y-m-d\TH:i:s\Z'),
            ], Response::HTTP_CREATED);
        } catch (ScreeningNotFound) {
            return response()->json([
                'message' => 'Сеанс не найден.',
            ], Response::HTTP_NOT_FOUND);
        } catch (ScreeningUnavailable) {
            return response()->json([
                'message' => 'Сеанс уже начался и недоступен для бронирования.',
            ], Response::HTTP_CONFLICT);
        } catch (NoAvailableSeats) {
            return response()->json([
                'message' => 'Свободных мест на сеанс не осталось.',
            ], Response::HTTP_CONFLICT);
        }
    }
}
