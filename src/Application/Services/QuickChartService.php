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

    public function __construct(private Redis $redis, private QuickChart $quickchart)
    {
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
        $cacheKey = sprintf(self::CACHE_KEY, strtolower($pairEnum->value));

        if (!$this->redis->exists($cacheKey)) {
            $this->quickchart->setConfig(json_encode($chartConfig));
            $content = $this->quickchart->toBinary();
            $content = base64_encode($content);

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
