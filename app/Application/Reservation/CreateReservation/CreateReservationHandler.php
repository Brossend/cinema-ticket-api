<?php

declare(strict_types=1);

namespace App\Application\Reservation\CreateReservation;

use App\Application\Contracts\Clock;
use App\Application\Contracts\ReservationIdGenerator;
use App\Application\Contracts\ReservationTokenGenerator;
use App\Application\Contracts\TransactionManager;
use App\Application\Reservation\ReservationRepository;
use App\Application\Screening\Exception\ScreeningNotFound;
use App\Application\Screening\ScreeningRepository;
use App\Domain\Reservation\Reservation;
use DateInterval;

final readonly class CreateReservationHandler
{
    public function __construct(
        private TransactionManager $transactionManager,
        private Clock $clock,
        private ScreeningRepository $screeningRepository,
        private ReservationRepository $reservationRepository,
        private ReservationIdGenerator $reservationIdGenerator,
        private ReservationTokenGenerator $reservationTokenGenerator,
    ) {}

    public function handle(int $screeningId): CreateReservationResult
    {
        /** @var CreateReservationResult $result */
        $result = $this->transactionManager->run(
            function () use ($screeningId): CreateReservationResult {
                $now = $this->clock->now();

                $screening = $this->screeningRepository
                    ->findByIdForUpdate($screeningId);

                if ($screening === null) {
                    throw new ScreeningNotFound;
                }

                $screening->assertCanBeReservedAt($now);

                $this->reservationRepository
                    ->expirePendingForScreening($screeningId, $now);

                $occupiedSeats = $this->reservationRepository
                    ->countSeatOccupyingByScreeningId($screeningId);

                $screening->assertCanAcceptReservation($occupiedSeats);

                $reservationToken = $this->reservationTokenGenerator->generate();
                $expiresAt = $now->add(new DateInterval('PT2M'));

                $reservation = Reservation::createPending(
                    id: $this->reservationIdGenerator->generate(),
                    screeningId: $screeningId,
                    accessTokenHash: hash('sha256', $reservationToken),
                    expiresAt: $expiresAt,
                );

                $this->reservationRepository->save($reservation);

                return new CreateReservationResult(
                    id: $reservation->id,
                    reservationToken: $reservationToken,
                    expiresAt: $expiresAt,
                );
            },
        );

        return $result;
    }
}
