<?php

declare(strict_types=1);

namespace Application\Services;

use App\Application\Dto\DayRateDto;
use App\Application\Enums\CurrencyPairEnum;
use App\Application\Enums\TradeDirectionEnum;
use App\Application\Repositories\MongoUsdRepository;
use App\Application\Services\MongoDbService;
use App\Application\Storages\MongoUsdStorage;
use DateTime;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\CursorInterface;
use MongoException;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class MongoDbServiceTest extends TestCase
{
    private MongoDbService $service;
    private MockObject|MongoUsdStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = $this->getMockBuilder(MongoUsdStorage::class)->disableOriginalConstructor()->getMock();
        $repository = new MongoUsdRepository($this->storage);
        $this->service = new MongoDbService($repository);
    }

    public function testGetCurrencyPairChartValues(): void
    {
        $cursor = $this->getMockBuilder(CursorInterface::class)->getMock();
        $map = [
            ['_id' => 1, 'value'=> 5.1, 'date' => new UTCDateTime(DateTime::createFromFormat('m.d', '05.01'))],
            ['_id' => 2, 'value'=> 5.2, 'date' => new UTCDateTime(DateTime::createFromFormat('m.d', '05.01'))],
            ['_id' => 3, 'value'=> 5.2, 'date' => new UTCDateTime(DateTime::createFromFormat('m.d', '05.02'))],
            ['_id' => 4, 'value'=> 5.3, 'date' => new UTCDateTime(DateTime::createFromFormat('m.d', '05.02'))],
            ['_id' => 5, 'value'=> 5.3, 'date' => new UTCDateTime(DateTime::createFromFormat('m.d', '05.03'))],
            ['_id' => 6, 'value'=> 5.4, 'date' => new UTCDateTime(DateTime::createFromFormat('m.d', '05.03'))],
        ];
        $cursor->expects(self::once())->method('toArray')->willReturn($map);
        $this->storage->expects(self::once())->method('find')->willReturn($cursor);

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
