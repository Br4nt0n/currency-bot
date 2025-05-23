<?php

declare(strict_types=1);

namespace Application\Services;

use App\Application\Dto\ARSRatesDto;
use App\Application\Dto\RubDto;
use App\Application\Dto\RUBRatesDto;
use App\Application\Dto\UsdBlueDto;
use App\Application\Dto\UsdDto;
use App\Application\Dto\USDRatesDto;
use App\Application\Services\ConversionService;
use App\Application\Services\CurrencyServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Redis;
use Tests\TestCase;

class ConversionServiceTest extends TestCase
{
    private Redis|MockObject $redis;
    private CurrencyServiceInterface|MockObject $currencyService;
    private ConversionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->redis = $this->getMockBuilder(Redis::class)->getMock();
        $this->currencyService = $this->getMockBuilder(CurrencyServiceInterface::class)->getMock();
        $this->service = new ConversionService($this->redis, $this->currencyService);
    }

    public function testPesoConversion(): void
    {
        $amount = 1000;
        $this->redis->method('get')->willReturn(false);

        $usdBlueDto = new UsdBlueDto(buy: 4.1, sell: 5.1);
        $this->currencyService->method('getDollarBlueRate')->willReturn($usdBlueDto);

        $rubDto = new RubDto(rubUsd: 1.2, rubArs: 2.2);
        $this->currencyService->method('getRubRates')->willReturn($rubDto);

        $usdDto = new UsdDto(usdRub: 3.2, usdArs: 4.2);
        $this->currencyService->method('getUsdRates')->willReturn($usdDto);

        $this->redis->expects($this->exactly(3))->method('get')
            ->willReturnCallback(function ($key) use (&$calls) {
                $calls[] = [$key,];
                return true;
            });

        /** @var ARSRatesDto $result */
        $result = $this->service->pesoConversion($amount);

        $this->assertInstanceOf(ARSRatesDto::class, $result);
        $this->assertEquals(round($amount / $rubDto->rubArs, 2), $result->rub);
        $this->assertEquals(round($amount / $usdDto->usdArs, 2), $result->usd);
        $this->assertEquals(round($amount / $usdBlueDto->sell,2), $result->usd_blue);

        $this->assertSame([
            [CurrencyServiceInterface::DOLLAR_BLUE_SELL,],
            [CurrencyServiceInterface::RUB_ARS,],
            [CurrencyServiceInterface::USD_ARS,],
        ], $calls);
    }

    public function testDollarConversion(): void
    {
        $amount = 100;
        $this->redis->method('get')->willReturn(false);

        $usdBlueDto = new UsdBlueDto(buy: 4.1, sell: 5.1);
        $this->currencyService->method('getDollarBlueRate')->willReturn($usdBlueDto);

        $usdDto = new UsdDto(usdRub: 3.2, usdArs: 4.2);
        $this->currencyService->method('getUsdRates')->willReturn($usdDto);

        $this->redis->expects($this->exactly(3))->method('get')
            ->willReturnCallback(function ($key) use (&$calls) {
                $calls[] = [$key,];
                return true;
            });

        /** @var USDRatesDto $result */
        $result = $this->service->dollarConversion($amount);

        $this->assertInstanceOf(USDRatesDto::class, $result);
        $this->assertEquals(round($amount * $usdDto->usdArs, 2), $result->ars);
        $this->assertEquals(round($amount * $usdBlueDto->buy, 2), $result->ars_blue);
        $this->assertEquals(round($amount * $usdDto->usdRub,2), $result->rub);

        $this->assertSame([
            [CurrencyServiceInterface::DOLLAR_BLUE_BUY,],
            [CurrencyServiceInterface::USD_RUB,],
            [CurrencyServiceInterface::USD_ARS,],
        ], $calls);
    }

    public function testRubleConversion(): void
    {
        $amount = 1000;
        $this->redis->method('get')->willReturn(false);

        $rubDto = new RubDto(rubUsd: 1.2, rubArs: 2.2);
        $this->currencyService->method('getRubRates')->willReturn($rubDto);

        $this->redis->expects($this->exactly(2))->method('get')
            ->willReturnCallback(function ($key) use (&$calls) {
                $calls[] = [$key,];
                return true;
            });

        /** @var RUBRatesDto $result */
        $result = $this->service->rubleConversion($amount);

        $this->assertInstanceOf(RUBRatesDto::class, $result);
        $this->assertEquals(round($amount * $rubDto->rubArs, 2), $result->ars);
        $this->assertEquals(round($amount * $rubDto->rubUsd,2), $result->usd);

        $this->assertSame([
            [CurrencyServiceInterface::RUB_ARS,],
            [CurrencyServiceInterface::RUB_USD,],
        ], $calls);
    }
}
