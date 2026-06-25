<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

final class ScreeningModel extends Model
{
    protected $table = 'screenings';

    protected $fillable = [
        'title',
        'starts_at',
        'total_seats',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
        ];
    }
}
