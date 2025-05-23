<?php

declare(strict_types=1);

namespace Application\Services;

use App\Application\Dto\DayRateDto;
use App\Application\Enums\CurrencyPairEnum;
use App\Application\Enums\TradeDirectionEnum;
use App\Application\Repositories\MongoUsdRepository;
use App\Application\Services\MongoDbService;
use MongoException;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class MongoDbServiceTest extends TestCase
{
    private MongoUsdRepository|MockObject $repository;
    private MongoDbService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getMockBuilder(MongoUsdRepository::class)->disableOriginalConstructor()->getMock();
        $this->service = new MongoDbService($this->repository);
    }

    public function testSaveUsdRate(): void
    {
        $this->repository->method('saveDayRate')->willReturn(true);
        $result = $this->service->saveUsdRate(new DayRateDto(
            pair: CurrencyPairEnum::USD_RUB,
            direction: TradeDirectionEnum::BUY,
            value: 5.5
        ));

        $this->assertTrue($result);
    }

    public function testGetCurrencyPairChartValues(): void
    {
        $map = [
            ['id' => 1, 'value'=> 5.1, 'date' => '05.01'],
            ['id' => 2, 'value'=> 5.2, 'date' => '05.01'],
            ['id' => 3, 'value'=> 5.2, 'date' => '05.02'],
            ['id' => 4, 'value'=> 5.3, 'date' => '05.02'],
            ['id' => 5, 'value'=> 5.3, 'date' => '05.03'],
            ['id' => 6, 'value'=> 5.4, 'date' => '05.03'],
        ];
        $this->repository->method('getLastThirtyDays')->willReturn($map);

        $result = $this->service->getCurrencyPairChartValues(CurrencyPairEnum::USD_RUB);

        $expected = [
            'dates' => ['05.01', '05.02', '05.03'],
            'values' => [
                round((5.1 + 5.2) / 2, 2),
                round((5.2 + 5.3) / 2, 2),
                round((5.3 + 5.4) / 2, 2)
            ]
        ];

        $this->assertSame($expected, $result);
    }
}
