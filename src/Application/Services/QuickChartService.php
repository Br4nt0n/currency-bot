<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Enums\CurrencyPairEnum;
use QuickChart;
use Redis;

final class QuickChartService
{
    public const string CACHE_KEY = 'currency_%s_graph';

    private const int TTL = 43200;

    private QuickChart $quickchart;

    public function __construct(private Redis $redis)
    {
        $this->quickchart = new QuickChart();
        $this->quickchart->setWidth(700);
        $this->quickchart->setHeight(400);
        $this->quickchart->setBackgroundColor('white');
    }

    public function makeChart(array $dates, array $values, CurrencyPairEnum $pairEnum): void
    {
        $description = $pairEnum->description();
        $chartConfig = [
            'type' => 'line',
            'data' => $this->makeData($dates, $values, $pairEnum),
            'options' => [
                'title' => ['display' => true, 'text' => "Курс валют $description за 30 дней"],
                'plugins' => ['legend' => ['position' => 'bottom']],
            ]
        ];

        $configHash = hash('sha256', json_encode($chartConfig));
        $imagePath = "/tmp/graph_$configHash.png";
        $cacheKey = sprintf(self::CACHE_KEY, strtolower($pairEnum->value));

        if (!$this->redis->exists($cacheKey)) {
            $this->quickchart->setConfig(json_encode($chartConfig));
            $this->quickchart->toFile($imagePath);
            $content = base64_encode(file_get_contents($imagePath));

            $this->redis->setex($cacheKey, self::TTL, $content);
        }
    }

    private function makeData(array $labels, array $values, CurrencyPairEnum $pairEnum): array
    {
        return [
            'labels' => array_reverse($labels),
            'datasets' => [
                [
                    'label' => $pairEnum->value,
                    'data' => array_reverse($values),
                    'borderColor' => $pairEnum->color(),
                    'fill' => false,
                ],
            ]
        ];
    }
}
