<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\ScreeningModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

final class ScreeningSeeder extends Seeder
{
    public function run(): void
    {
        $firstDay = CarbonImmutable::now('Asia/Yekaterinburg')
            ->addDay()
            ->startOfDay();

        $screenings = [
            ['Интерстеллар', $firstDay->setTime(19, 0)],
            ['Дюна: Часть вторая', $firstDay->setTime(20, 30)],
            ['Бегущий по лезвию 2049', $firstDay->setTime(22, 10)],
            ['Начало', $firstDay->addDay()->setTime(12, 0)],
            ['Матрица', $firstDay->addDay()->setTime(14, 20)],
            ['Побег из Шоушенка', $firstDay->addDay()->setTime(16, 45)],
            ['Остров проклятых', $firstDay->addDay()->setTime(19, 10)],
            ['Темный рыцарь', $firstDay->addDay()->setTime(21, 40)],
            ['Зеленая миля', $firstDay->addDays(2)->setTime(13, 30)],
            ['Ла-Ла Ленд', $firstDay->addDays(2)->setTime(18, 0)],
        ];

        foreach ($screenings as [$title, $startsAt]) {
            ScreeningModel::query()->updateOrCreate(
                [
                    'title' => $title,
                ],
                [
                    'starts_at' => $startsAt->setTimezone('UTC'),
                    'total_seats' => 10,
                ],
            );
        }
    }
}
