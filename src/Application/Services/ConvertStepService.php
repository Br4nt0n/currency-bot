<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Dto\RatesBase;
use App\Application\Enums\CurrencyCodeEnum;
use Redis;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

final class ConvertStepService
{
    private const string CHAT_ID = 'chat_%s';

    public function __construct(private ConversionInterface $conversion, private Redis $redis)
    {
    }

    public function handle(Api $telegram, Update $update)
    {
        $message = $update->getMessage();
        $chatId = $update->getChat()->get('id');
        $text = $message->get('text');
        $cacheKey = sprintf(self::CHAT_ID, $chatId);

        if (in_array($text, CurrencyCodeEnum::values())) {
            $this->redis->set($cacheKey, $text);

            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Вы выбрали: $text Введите требуемую сумму",
            ]);

            return true;
        }

        if ($this->redis->get($cacheKey) !== false) {
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
                    'text' => "Введите корректную сумму " . $this->redis->get($chatId),
                ]);
            }

            return true;
        }

        return false;
    }

    private function calculateCurrency(float $amount, string $currency): RatesBase
    {
        $result = match ($currency) {
            CurrencyCodeEnum::ARS->value => $this->conversion->pesoConversion($amount),
            CurrencyCodeEnum::USD->value => $this->conversion->dollarConversion($amount),
            CurrencyCodeEnum::RUB->value => $this->conversion->rubleConversion($amount),
        };

        return  $result;
    }

}
