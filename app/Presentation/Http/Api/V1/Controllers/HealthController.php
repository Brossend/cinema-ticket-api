<?php

namespace App\Presentation\Http\Api\V1\Controllers;

use Illuminate\Http\JsonResponse;

final class HealthController
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }
}
