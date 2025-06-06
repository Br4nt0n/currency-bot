<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\Enums\CurrencyPairEnum;
use App\Infrastructure\Repositories\Http\MongoUsdCurrencyRepository;
use MongoException;

final readonly class MongoDbService
{
    public function __construct(private MongoUsdCurrencyRepository $usdRepository)
    {
    }

    public function getCurrencyPairChartValues(CurrencyPairEnum $pairEnum): array
    {
        $data = $this->usdRepository->getLatestFor(30, $pairEnum);
        $grouppedData = $this->groupByDateAverage($data);

        $dates = [];
        $values = [];
        foreach ($grouppedData as $item) {
            $dates[] = $item['date'];
            $values[] = $item['average_value'];
        }

        if (count($dates) !== count($values)) {
            throw new MongoException("Currency for pair: $pairEnum->value are wrong!");
        }

        return [
            'dates' => $dates,
            'values' => $values,
        ];
    }

    private function groupByDateAverage(array $data): array
    {
        $grouped = [];

        foreach ($data as $item) {
            $date = $item['date'];
            if (!isset($grouped[$date])) {
                $grouped[$date] = ['sum' => 0, 'count' => 0];
            }
            $grouped[$date]['sum'] += $item['value'];
            $grouped[$date]['count'] += 1;
        }

        // Теперь считаем средние значения
        $result = [];

        foreach ($grouped as $date => $stats) {
            $average = round($stats['sum'] / $stats['count'], 2);
            $result[] = ['date' => $date, 'average_value' => $average];
        }

        return $result;
    }
}
