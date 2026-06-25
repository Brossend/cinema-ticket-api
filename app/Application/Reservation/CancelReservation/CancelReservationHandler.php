<?php

declare(strict_types=1);

namespace App\Application\Reservation\CancelReservation;

use App\Application\Contracts\Clock;
use App\Application\Contracts\TransactionManager;
use App\Application\Reservation\Exception\ReservationAccessDenied;
use App\Application\Reservation\Exception\ReservationNotFound;
use App\Application\Reservation\ReservationRepository;
use App\Application\Screening\ScreeningRepository;

final readonly class CancelReservationHandler
{
    public function __construct(
        private TransactionManager $transactionManager,
        private Clock $clock,
        private ScreeningRepository $screeningRepository,
        private ReservationRepository $reservationRepository,
    ) {}

    public function handle(
        CancelReservationCommand $command,
    ): CancelReservationResult {
        /** @var CancelReservationResult $result */
        $result = $this->transactionManager->run(
            function () use ($command): CancelReservationResult {
                $reservation = $this->reservationRepository
                    ->findById($command->reservationId);

                if ($reservation === null) {
                    throw new ReservationNotFound;
                }

                $incomingTokenHash = hash(
                    'sha256',
                    $command->reservationToken,
                );

                if (
                    ! hash_equals(
                        $reservation->accessTokenHash,
                        $incomingTokenHash,
                    )
                ) {
                    throw new ReservationAccessDenied;
                }

                $screening = $this->screeningRepository
                    ->findByIdForUpdate($reservation->screeningId);

                if ($screening === null) {
                    throw new ReservationNotFound;
                }

                $reservation = $this->reservationRepository
                    ->findByIdForUpdate($command->reservationId);

                if ($reservation === null) {
                    throw new ReservationNotFound;
                }

                if (
                    ! hash_equals(
                        $reservation->accessTokenHash,
                        $incomingTokenHash,
                    )
                ) {
                    throw new ReservationAccessDenied;
                }

                $now = $this->clock->now();

                if ($reservation->expire($now)) {
                    $this->reservationRepository->save($reservation);

                    return new CancelReservationResult(
                        id: $reservation->id,
                        status: $reservation->status(),
                    );
                }

                $reservation->cancel($now);

                $this->reservationRepository->save($reservation);

                return new CancelReservationResult(
                    id: $reservation->id,
                    status: $reservation->status(),
                );
            },
        );

        return $result;
    }
}
