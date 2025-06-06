<?php

declare(strict_types=1);

namespace Infrastructure\Services;

use App\Application\Enums\CurrencyCodeEnum;
use App\Application\Services\ConversionInterface;
use App\Application\ValueObjects\ARSRates;
use App\Application\ValueObjects\RUBRates;
use App\Application\ValueObjects\USDRates;
use App\Infrastructure\Services\ConvertStepService;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Redis;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Tests\TestCase;

class ConvertStepServiceTest extends TestCase
{
    private ConvertStepService $service;

    private Redis|MockObject $redis;

    private ConversionInterface|MockObject $conversionService;

    private MockObject|Api $api;

    private MockObject|Update $update;

    protected function setUp(): void
    {
        parent::setUp();
        $this->api = $this->getMockBuilder(Api::class)->disableOriginalConstructor()->getMock();
        $this->update = $this->getMockBuilder(Update::class)->disableOriginalConstructor()->getMock();

        $this->redis = $this->getMockBuilder(Redis::class)->getMock();
        $this->conversionService = $this->getMockBuilder(ConversionInterface::class)->getMock();

        $this->service = new ConvertStepService(
            $this->conversionService,
            $this->redis
        );
    }

    public function testHandleUsdSuccess(): void
    {
        $amount = 100;
        $currency = CurrencyCodeEnum::USD->value;
        $messages = collect(['text' => $amount]);
        $this->update->method('getMessage')->willReturn($messages);

        $chatId = 111;
        $chat = collect(['id' => $chatId]);
        $this->update->method('getChat')->willReturn($chat);

        $this->redis->expects(self::once())->method('exists')->willReturn(true);
        $this->redis->expects(self::once())->method('get')->willReturn($currency);
        $this->redis->expects(self::once())->method('del');

        $this->api->expects(self::exactly(2))->method('sendMessage')
            ->willReturnCallback(function ($message) use (&$calls) {
                $calls[] = $message;
                return new Message($message);
            });

        $dto = new USDRates(
            ars: 2.2,
            ars_blue: 2.3,
            rub: 2.4
        );
        $this->conversionService->method('dollarConversion')->willReturn($dto);
        $this->conversionService->expects(self::never())->method('pesoConversion');
        $this->conversionService->expects(self::never())->method('rubleConversion');

        $result = $this->service->handle($this->api, $this->update);

        $this->assertTrue($result);
        $this->assertSame([
            ['chat_id' => $chatId, 'text' =>  "Вы ввели: $amount $currency"],
            ['chat_id' => $chatId, 'text' =>  $dto->toString()],
        ], $calls);

    }

    public function testHandleRubSuccess(): void
    {
        $amount = 100;
        $currency = CurrencyCodeEnum::RUB->value;
        $messages = collect(['text' => $amount]);
        $this->update->method('getMessage')->willReturn($messages);

        $chatId = 111;
        $chat = collect(['id' => $chatId]);
        $this->update->method('getChat')->willReturn($chat);

        $this->redis->expects(self::once())->method('exists')->willReturn(true);
        $this->redis->expects(self::once())->method('get')->willReturn($currency);
        $this->redis->expects(self::once())->method('del');

        $this->api->expects(self::exactly(2))->method('sendMessage')
            ->willReturnCallback(function ($message) use (&$calls) {
                $calls[] = $message;
                return new Message($message);
            });

        $dto = new RUBRates(
            ars: 3.3,
            usd: 3.4
        );
        $this->conversionService->method('rubleConversion')->willReturn($dto);
        $this->conversionService->expects(self::never())->method('pesoConversion');
        $this->conversionService->expects(self::never())->method('dollarConversion');

        $result = $this->service->handle($this->api, $this->update);

        $this->assertTrue($result);
        $this->assertSame([
            ['chat_id' => $chatId, 'text' =>  "Вы ввели: $amount $currency"],
            ['chat_id' => $chatId, 'text' =>  $dto->toString()],
        ], $calls);
    }

    public function testHandleArsSuccess(): void
    {
        $amount = 100;
        $currency = CurrencyCodeEnum::ARS->value;
        $messages = collect(['text' => $amount]);
        $this->update->method('getMessage')->willReturn($messages);

        $chatId = 111;
        $chat = collect(['id' => $chatId]);
        $this->update->method('getChat')->willReturn($chat);

        $this->redis->expects(self::once())->method('exists')->willReturn(true);
        $this->redis->expects(self::once())->method('get')->willReturn($currency);
        $this->redis->expects(self::once())->method('del');

        $this->api->expects(self::exactly(2))->method('sendMessage')
            ->willReturnCallback(function ($message) use (&$calls) {
                $calls[] = $message;
                return new Message($message);
            });

        $dto = new ARSRates(
            rub: 1.1,
            usd: 1.2,
            usd_blue: 1.3
        );
        $this->conversionService->method('pesoConversion')->willReturn($dto);
        $this->conversionService->expects(self::never())->method('rubleConversion');
        $this->conversionService->expects(self::never())->method('dollarConversion');

        $result = $this->service->handle($this->api, $this->update);

        $this->assertTrue($result);
        $this->assertSame([
            ['chat_id' => $chatId, 'text' =>  "Вы ввели: $amount $currency"],
            ['chat_id' => $chatId, 'text' =>  $dto->toString()],
        ], $calls);
    }

    public function testHandleTestFalse(): void
    {
        $amount = 100;
        $messages = collect(['text' => $amount]);
        $this->update->method('getMessage')->willReturn($messages);

        $chatId = 111;
        $chat = collect(['id' => $chatId]);
        $this->update->method('getChat')->willReturn($chat);

        // Ключа нет в кэше
        $this->redis->expects(self::once())->method('exists')->willReturn(false);
        $this->conversionService->expects(self::never())->method('dollarConversion');
        $this->conversionService->expects(self::never())->method('pesoConversion');
        $this->conversionService->expects(self::never())->method('rubleConversion');

        $result = $this->service->handle($this->api, $this->update);

        $this->assertFalse($result);
    }

    public function testHandleAmountWrong(): void
    {
        $amount = 'wrong';
        $messages = collect(['text' => $amount]);
        $this->update->method('getMessage')->willReturn($messages);

        $chatId = 111;
        $chat = collect(['id' => $chatId]);
        $this->update->method('getChat')->willReturn($chat);

        $this->redis->expects(self::once())->method('exists')->willReturn(true);
        $this->conversionService->expects(self::never())->method('dollarConversion');
        $this->conversionService->expects(self::never())->method('pesoConversion');
        $this->conversionService->expects(self::never())->method('rubleConversion');

        $this->api->expects(self::once())
            ->method('sendMessage')
            ->with(['chat_id' => $chatId, 'text' => "Введите корректную сумму"]);

        $result = $this->service->handle($this->api, $this->update);

        $this->assertTrue($result);
    }

    public function testHandleWrongCurrency(): void
    {
        $amount = 100;
        $currency = 'wrong currency';
        $messages = collect(['text' => $amount]);
        $this->update->method('getMessage')->willReturn($messages);

        $chatId = 111;
        $chat = collect(['id' => $chatId]);
        $this->update->method('getChat')->willReturn($chat);

        $this->redis->expects(self::once())->method('exists')->willReturn(true);
        $this->redis->expects(self::once())->method('get')->willReturn($currency);
        $this->redis->expects(self::once())->method('del');

        $this->expectException(InvalidArgumentException::class);
        $this->service->handle($this->api, $this->update);
    }
}
