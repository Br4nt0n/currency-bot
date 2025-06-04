<?php

declare(strict_types=1);

namespace Application\Services;

use App\Application\Enums\CacheKeyEnum;
use App\Application\Enums\CurrencyPairEnum;
use App\Application\Services\QuickChartService;
use PHPUnit\Framework\MockObject\MockObject;
use QuickChart;
use Redis;
use Tests\TestCase;

class QuickChartServiceTest extends TestCase
{
    private QuickChartService $service;
    private Redis|MockObject $redis;
    private QuickChart|MockObject $quickchart;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quickchart = $this->getMockBuilder(QuickChart::class)->getMock();
        $this->redis = $this->getMockBuilder(Redis::class)->getMock();
        $this->service = new QuickChartService($this->redis, $this->quickchart);
    }

    public function testMakeChart(): void
    {
        $dates = ['05.01', '05.02', '05.03'];
        $values = [1.1, 2.2, 2.3, 2.4];
        $config = $this->makeData($dates, $values, CurrencyPairEnum::USD_RUB);

        $this->quickchart->method('toBinary')->willReturn("111");
        $this->redis->method('exists')->willReturn(false);
        $this->redis->expects(self::once())->method('setex')
            ->with(
                CacheKeyEnum::GRAPH->format(CurrencyPairEnum::USD_RUB->value),
                43200,
                base64_encode("111")
            );

        $this->quickchart->expects(self::once())->method('setConfig')->with(json_encode($config));
        $this->quickchart->expects(self::once())->method('toBinary');


        $this->service->makeChart($dates, $values, CurrencyPairEnum::USD_RUB);

    }

    private function makeData(array $dates, array $values, CurrencyPairEnum $pairEnum): array
    {
        $description = $pairEnum->description();
        return [
            'type' => 'line',
            'data' => [
                'labels' => array_reverse($dates),
                'datasets' => [
                    [
                        'label' => $pairEnum->value,
                        'data' => array_reverse($values),
                        'borderColor' => $pairEnum->color(),
                        'fill' => false,
                    ],
                ]
            ],
            'options' => [
                'title' => ['display' => true, 'text' => "Курс валют $description за 30 дней"],
                'plugins' => ['legend' => ['position' => 'bottom']],
                'scales' => [
                    'yAxes' => [
                        'ticks' => [
                            'callback' => "function(val) { return val'; }"
                        ]
                    ]
                ],
            ]
        ];

    }
}
