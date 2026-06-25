<?php

declare(strict_types=1);

namespace App\Presentation\Http\Api\V1\Controllers;

use App\Application\Reservation\Exception\ReservationAccessDenied;
use App\Application\Reservation\Exception\ReservationNotFound;
use App\Application\Reservation\PayReservation\PayReservationCommand;
use App\Application\Reservation\PayReservation\PayReservationHandler;
use App\Domain\Reservation\Exception\ReservationCannotBePaid;
use App\Domain\Reservation\Exception\ReservationExpired;
use App\Presentation\Http\Api\V1\Requests\PayReservationRequest;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class PayReservationController
{
    public function __construct(
        private PayReservationHandler $payReservationHandler,
    ) {}

    public function __invoke(
        PayReservationRequest $request,
        string $reservation,
    ): JsonResponse {
        $validated = $request->validated();

        try {
            $result = $this->payReservationHandler->handle(
                new PayReservationCommand(
                    reservationId: $reservation,
                    reservationToken: $request->reservationToken(),
                    customerName: $validated['name'],
                    customerEmail: $validated['email'],
                ),
            );

            return response()->json([
                'id' => $result->id,
                'status' => $result->status,
                'paidAt' => $result->paidAt
                    ->setTimezone(new DateTimeZone('UTC'))
                    ->format('Y-m-d\TH:i:s\Z'),
            ]);
        } catch (ReservationNotFound) {
            return response()->json([
                'message' => 'Бронь не найдена.',
            ], Response::HTTP_NOT_FOUND);
        } catch (ReservationAccessDenied) {
            return response()->json([
                'message' => 'Недействительный токен бронирования.',
            ], Response::HTTP_FORBIDDEN);
        } catch (ReservationExpired) {
            return response()->json([
                'message' => 'Срок действия брони истёк.',
            ], Response::HTTP_CONFLICT);
        } catch (ReservationCannotBePaid) {
            return response()->json([
                'message' => 'Бронь уже обработана и не может быть оплачена.',
            ], Response::HTTP_CONFLICT);
        }
    }
}
