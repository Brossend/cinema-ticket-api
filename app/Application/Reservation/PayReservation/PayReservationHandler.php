<?php

declare(strict_types=1);

namespace App\Application\Reservation\PayReservation;

use App\Application\Contracts\Clock;
use App\Application\Contracts\TransactionManager;
use App\Application\Reservation\Exception\ReservationAccessDenied;
use App\Application\Reservation\Exception\ReservationNotFound;
use App\Application\Reservation\ReservationRepository;
use App\Application\Screening\Exception\ScreeningNotFound;
use App\Application\Screening\ScreeningRepository;
use App\Domain\Reservation\Customer;

final readonly class PayReservationHandler
{
    public function __construct(
        private TransactionManager $transactionManager,
        private Clock $clock,
        private ScreeningRepository $screeningRepository,
        private ReservationRepository $reservationRepository,
    ) {}

    public function handle(
        PayReservationCommand $command,
    ): PayReservationResult {
        /** @var PayReservationResult $result */
        $result = $this->transactionManager->run(
            function () use ($command): PayReservationResult {
                $reservation = $this->reservationRepository
                    ->findById($command->reservationId);

                if ($reservation === null) {
                    throw new ReservationNotFound;
                }

                $screening = $this->screeningRepository
                    ->findByIdForUpdate($reservation->screeningId);

                if ($screening === null) {
                    throw new ScreeningNotFound;
                }

                $reservation = $this->reservationRepository
                    ->findByIdForUpdate($command->reservationId);

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

                $now = $this->clock->now();

                $reservation->pay(
                    customer: new Customer(
                        name: $command->customerName,
                        email: $command->customerEmail,
                    ),
                    now: $now,
                );

                $this->reservationRepository->save($reservation);

                return new PayReservationResult(
                    id: $reservation->id,
                    status: $reservation->status()->value,
                    paidAt: $now,
                );
            },
        );

        return $result;
    }
}
