<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Enums\CurrencyCodeEnum;
use DI\Container;
use Redis;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

class ConvertStepHandler
{
    public static function handle(Api $telegram, Update $update, Redis $redis): bool
    {
        $message = $update->getMessage();
        $chatId = $update->getChat()->get('id');
        $text = $message->get('text');

        if (in_array($text, CurrencyCodeEnum::values())) {
            $redis->set('chat_' . $chatId, $text);

            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Вы выбрали: $text Введите требуемую сумму",
            ]);

            return true;
        }

        if ($redis->get('chat_' . $chatId) !== false) {
            if (is_numeric($text) && $text > 0) {
                $amount = floatval($text);
                $currency = $redis->get('chat_' . $chatId);

                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Вы ввели: $amount $currency",
                ]);

            } else {
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Введите корректную сумму " . $redis->get('chat_' . $chatId),
                ]);
            }

            return true;
        }

        return false;
    }

    private function calculateCurrency(float $amount, string $currency): float
    {
        return 0.0;
    }
}
