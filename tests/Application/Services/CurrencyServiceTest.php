<?php

declare(strict_types=1);

namespace Application\Services;

use App\Application\Dto\RubDto;
use App\Application\Dto\UsdBlueDto;
use App\Application\Dto\UsdDto;
use App\Application\Exceptions\CurrencyException;
use App\Application\Repositories\BlueLyticsRepository;
use App\Application\Repositories\ExchangeRateRepository;
use App\Application\Services\CurrencyService;
use App\Application\Services\CurrencyServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use Redis;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    private CurrencyService $service;
    private MockObject|Redis $redis;
    private MockObject|ExchangeRateRepository $exchangeRateRepository;
    private BlueLyticsRepository|MockObject $blueRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->exchangeRateRepository = $this->getMockBuilder(ExchangeRateRepository::class)->disableOriginalConstructor()->getMock();
        $this->blueRepository = $this->getMockBuilder(BlueLyticsRepository::class)->disableOriginalConstructor()->getMock();
        $this->redis = $this->getMockBuilder(Redis::class)->getMock();

        $this->service = new CurrencyService(
            $this->exchangeRateRepository,
            $this->blueRepository,
            $this->redis,
        );
    }

    public function testGetUsdRatesSuccess(): void
    {
        $usdDto = new UsdDto(2.1, 2.3);
        $this->exchangeRateRepository->method('getUsdRate')->willReturn($usdDto);

        $this->redis->expects(self::exactly(2))->method('set')
            ->willReturnCallback(function ($key, $value, $ttl) use (&$calls) {
            $calls[] = [$key, $value, $ttl];
            return true;
        });
        $usdDto = $this->service->getUsdRates();

        $this->assertSame([
            [CurrencyServiceInterface::USD_ARS, $usdDto->usdArs, 43200],
            [CurrencyServiceInterface::USD_RUB, $usdDto->usdRub, 43200],
        ], $calls);
        $this->assertInstanceOf(UsdDto::class, $usdDto);
        $this->assertEquals(2.1, $usdDto->usdRub);
        $this->assertEquals( 2.3, $usdDto->usdArs);
    }

    public function testGetUsdRatesFail(): void
    {
        $usdDto = new UsdDto(null, null);
        $this->exchangeRateRepository->method('getUsdRate')->willReturn($usdDto);

        $this->expectException(CurrencyException::class);
        $this->redis->expects(self::never())->method('set')->with();
        $this->service->getUsdRates();
    }

    public function testGetRubRatesSuccess(): void
    {
        $rubDto = new RubDto(1.1, 1.3);
        $this->exchangeRateRepository->method('getRubRate')->willReturn($rubDto);

        $this->redis->expects(self::exactly(2))->method('set')
            ->willReturnCallback(function ($key, $value, $ttl) use (&$calls) {
            $calls[] = [$key, $value, $ttl];
            return true;
        });
        $rubDto = $this->service->getRubRates();

        $this->assertSame([
            [CurrencyServiceInterface::RUB_ARS, $rubDto->rubArs, 43200],
            [CurrencyServiceInterface::RUB_USD, $rubDto->rubUsd, 43200],
        ], $calls);
        $this->assertInstanceOf(RubDto::class, $rubDto);
        $this->assertEquals(1.1, $rubDto->rubUsd);
        $this->assertEquals( 1.3, $rubDto->rubArs);
    }

    public function testGetRubRatesFail(): void
    {
        $rubDto = new RubDto(null, null);
        $this->exchangeRateRepository->method('getRubRate')->willReturn($rubDto);

        $this->expectException(CurrencyException::class);
        $this->redis->expects(self::never())->method('set')->with();
        $this->service->getRubRates();
    }

    public function testGetDollarBlueRate(): void
    {
        $dto = new UsdBlueDto(2.2, 2.4);
        $this->blueRepository->method('getDollarBlueRates')->willReturn($dto);

        /** @var InvokedCount $matcher */
        $matcher = $this->exactly(2);
        $this->redis->expects($matcher)
            ->method('set')
            ->willReturnCallback(function ($key, $value, $ttl) use (&$calls) {
                $calls[] = [$key, $value, $ttl];
                return true;
            });
        $dto = $this->service->getDollarBlueRate();

        $this->assertSame([
            [CurrencyServiceInterface::DOLLAR_BLUE_BUY, $dto->buy, 43200],
            [CurrencyServiceInterface::DOLLAR_BLUE_SELL, $dto->sell, 43200],
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
}
