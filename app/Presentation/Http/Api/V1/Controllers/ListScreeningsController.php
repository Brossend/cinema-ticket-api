<?php

declare(strict_types=1);

namespace App\Presentation\Http\Api\V1\Controllers;

use App\Application\Screening\ListScreenings\ListScreeningsHandler;
use App\Presentation\Http\Api\V1\Resources\ScreeningResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class ListScreeningsController
{
    public function __construct(
        private ListScreeningsHandler $listScreeningsHandler,
    ) {}

    public function __invoke(): AnonymousResourceCollection
    {
        return ScreeningResource::collection(
            $this->listScreeningsHandler->handle(),
        );
    }
}
