<?php

declare(strict_types=1);

namespace Infrastructure\Services;

use App\Application\Dto\EurDto;
use App\Application\Dto\RubDto;
use App\Application\Dto\UsdBlueDto;
use App\Application\Dto\UsdDto;
use App\Application\Exceptions\CurrencyException;
use App\Application\Services\CurrencyServiceInterface;
use App\Infrastructure\Api\HttpClient\ExchangeRateClient;
use App\Infrastructure\Repositories\Http\BlueLyticsRepository;
use App\Infrastructure\Repositories\Http\ExchangeRateRepository;
use App\Infrastructure\Services\CurrencyService;
use PHPUnit\Framework\MockObject\MockObject;
use Redis;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    private CurrencyService $service;
    private MockObject|Redis $redis;
    private BlueLyticsRepository|MockObject $blueRepository;
    private ExchangeRateClient|MockObject $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->getMockBuilder(ExchangeRateClient::class)->disableOriginalConstructor()->getMock();
        $this->blueRepository = $this->getMockBuilder(BlueLyticsRepository::class)->disableOriginalConstructor()->getMock();
        $this->redis = $this->getMockBuilder(Redis::class)->getMock();

        $this->service = new CurrencyService(
            new ExchangeRateRepository($this->client),
            $this->blueRepository,
            $this->redis,
        );
    }

    public function testGetUsdRatesSuccess(): void
    {
        $result['quotes']['USDRUB'] = 2.1;
        $result['quotes']['USDARS'] = 2.3;
        $this->client->method('getLiveUsdRate')->willReturn($result);

        $this->redis->expects(self::exactly(2))->method('set')
            ->willReturnCallback(function ($key, $value, $ttl) use (&$calls) {
            $calls[] = [$key, $value, $ttl];
            return true;
        });
        $usdDto = $this->service->getUsdRates();

        $this->assertSame([
            [CurrencyServiceInterface::USD_ARS, $usdDto->usdArs, 86400],
            [CurrencyServiceInterface::USD_RUB, $usdDto->usdRub, 86400],
        ], $calls);
        $this->assertInstanceOf(UsdDto::class, $usdDto);
        $this->assertEquals(2.1, $usdDto->usdRub);
        $this->assertEquals( 2.3, $usdDto->usdArs);
    }

    public function testGetUsdRatesFail(): void
    {
        $result['quotes']['USDRUB'] = null;
        $result['quotes']['USDARS'] = null;
        $this->client->method('getLiveUsdRate')->willReturn($result);

        $this->expectException(CurrencyException::class);
        $this->redis->expects(self::never())->method('set')->with();
        $this->service->getUsdRates();
    }

    public function testGetRubRatesSuccess(): void
    {
        $result['quotes']['RUBUSD'] = 1.1;
        $result['quotes']['RUBARS'] = 1.3;
        $this->client->method('getLiveRubRate')->willReturn($result);

        $this->redis->expects(self::exactly(2))->method('set')
            ->willReturnCallback(function ($key, $value, $ttl) use (&$calls) {
            $calls[] = [$key, $value, $ttl];
            return true;
        });
        $rubDto = $this->service->getRubRates();

        $this->assertSame([
            [CurrencyServiceInterface::RUB_ARS, $rubDto->rubArs, 86400],
            [CurrencyServiceInterface::RUB_USD, $rubDto->rubUsd, 86400],
        ], $calls);
        $this->assertInstanceOf(RubDto::class, $rubDto);
        $this->assertEquals(1.1, $rubDto->rubUsd);
        $this->assertEquals( 1.3, $rubDto->rubArs);
    }

    public function testGetRubRatesFail(): void
    {
        $result['quotes']['RUBUSD'] = null;
        $result['quotes']['RUBARS'] = null;
        $this->client->method('getLiveRubRate')->willReturn($result);

        $this->expectException(CurrencyException::class);
        $this->redis->expects(self::never())->method('set')->with();
        $this->service->getRubRates();
    }

    public function testGetDollarBlueRate(): void
    {
        $dto = new UsdBlueDto(2.2, 2.4);
        $this->blueRepository->method('getDollarBlueRates')->willReturn($dto);

        $matcher = $this->exactly(2);
        $this->redis->expects($matcher)
            ->method('set')
            ->willReturnCallback(function ($key, $value, $ttl) use (&$calls) {
                $calls[] = [$key, $value, $ttl];
                return true;
            });
        $dto = $this->service->getDollarBlueRate();

        $this->assertSame([
            [CurrencyServiceInterface::DOLLAR_BLUE_BUY, $dto->buy, 86400],
            [CurrencyServiceInterface::DOLLAR_BLUE_SELL, $dto->sell, 86400],
        ], $calls);
        $this->assertInstanceOf(UsdBlueDto::class, $dto);
        $this->assertEquals(2.2, $dto->buy);
        $this->assertEquals( 2.4, $dto->sell);
    }

    public function testGetDollarBlueRateFail(): void
    {
        $dto = new UsdBlueDto(null, null);
        $this->blueRepository->method('getDollarBlueRates')->willReturn($dto);

        $this->expectException(CurrencyException::class);
        $this->redis->expects(self::never())->method('set')->with();
        $this->service->getDollarBlueRate();
    }

    public function testGetEurRatesSuccess(): void
    {
        $result['quotes']['EURRUB'] = 3.1;
        $result['quotes']['EURARS'] = 3.3;
        $this->client->method('getLiveEurRate')->willReturn($result);

        $this->redis->expects(self::exactly(2))->method('set')
            ->willReturnCallback(function ($key, $value, $ttl) use (&$calls) {
                $calls[] = [$key, $value, $ttl];
                return true;
            });
        $eurDto = $this->service->getEurRates();

        $this->assertSame([
            [CurrencyServiceInterface::EUR_RUB, $eurDto->eurRub, 86400],
            [CurrencyServiceInterface::EUR_ARS, $eurDto->eurArs, 86400],
        ], $calls);
        $this->assertInstanceOf(EurDto::class, $eurDto);
        $this->assertEquals(3.1, $eurDto->eurRub);
        $this->assertEquals( 3.3, $eurDto->eurArs);
    }

    public function testGetEurRatesFail(): void
    {
        $result['quotes']['EURRUB'] = null;
        $result['quotes']['EURARS'] = null;
        $this->client->method('getLiveEurRate')->willReturn($result);

        $this->expectException(CurrencyException::class);
        $this->redis->expects(self::never())->method('set')->with();
        $this->service->getEurRates();
    }
}
