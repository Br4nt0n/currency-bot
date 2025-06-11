<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\ValueObjects\RatesBase;
use App\Application\Enums\CurrencyCodeEnum;
use App\Application\Services\ConversionInterface;
use InvalidArgumentException;
use Redis;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

final class ConvertStepService
{
    private const string CHAT_ID = 'chat_%s';

    public function __construct(private readonly ConversionInterface $conversion, private readonly Redis $redis)
    {
    }

    public function handle(Api $telegram, Update $update): bool
    {
        $message = $update->getMessage();
        $chatId = $update->getChat()->get('id');
        $text = $message->get('text');
        $cacheKey = sprintf(self::CHAT_ID, $chatId);

        if ($this->redis->exists($cacheKey)) {
            if (is_numeric($text) && $text > 0) {
                $amount = floatval($text);
                $currency = $this->redis->get($cacheKey);
                $this->redis->del($cacheKey);

                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Вы ввели: $amount $currency",
                ]);

                $result = $this->calculateCurrency($amount, $currency);

                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $result->toString(),
                ]);

            } else {
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Введите корректную сумму",
                ]);
            }

            return true;
        }

        return false;
    }

    private function calculateCurrency(float $amount, string $currency): RatesBase
    {
        return match ($currency) {
            CurrencyCodeEnum::ARS->value => $this->conversion->pesoConversion($amount),
            CurrencyCodeEnum::USD->value => $this->conversion->dollarConversion($amount),
            CurrencyCodeEnum::RUB->value => $this->conversion->rubleConversion($amount),
            CurrencyCodeEnum::EUR->value => $this->conversion->euroConversion($amount),
            default => throw new InvalidArgumentException("This: $currency does`t exists!")
        };
    }

}
