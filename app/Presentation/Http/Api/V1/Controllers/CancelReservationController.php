<?php

declare(strict_types=1);

namespace App\Presentation\Http\Api\V1\Controllers;

use App\Application\Reservation\CancelReservation\CancelReservationCommand;
use App\Application\Reservation\CancelReservation\CancelReservationHandler;
use App\Application\Reservation\Exception\ReservationAccessDenied;
use App\Application\Reservation\Exception\ReservationNotFound;
use App\Domain\Reservation\Exception\ReservationCannotBeCancelled;
use App\Domain\Reservation\ReservationStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class CancelReservationController
{
    public function __construct(
        private CancelReservationHandler $cancelReservationHandler,
    ) {}

    public function __invoke(
        Request $request,
        string $reservation,
    ): JsonResponse {
        try {
            $result = $this->cancelReservationHandler->handle(
                new CancelReservationCommand(
                    reservationId: $reservation,
                    reservationToken: (string) $request->header(
                        'X-Reservation-Token',
                    ),
                ),
            );

            if ($result->status === ReservationStatus::Expired) {
                return response()->json([
                    'message' => 'Срок действия брони истёк.',
                ], Response::HTTP_CONFLICT);
            }

            return response()->json([
                'id' => $result->id,
                'status' => $result->status->value,
            ]);
        } catch (ReservationNotFound) {
            return response()->json([
                'message' => 'Бронь не найдена.',
            ], Response::HTTP_NOT_FOUND);
        } catch (ReservationAccessDenied) {
            return response()->json([
                'message' => 'Недействительный токен бронирования.',
            ], Response::HTTP_FORBIDDEN);
        } catch (ReservationCannotBeCancelled) {
            return response()->json([
                'message' => 'Бронь уже обработана и не может быть отменена.',
            ], Response::HTTP_CONFLICT);
        }
    }
}
