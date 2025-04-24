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
    public static function handle(Api $telegram, Update $update): bool
    {
        $message = $update->getMessage();
        $chatId = $update->getChat()->get('id');
        $text = $message->get('text');

        if (in_array($text, CurrencyCodeEnum::values())) {
            $container = new Container();
            $redis = $container->get(Redis::class);

            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Вы выбрали: $text Введите требуемую сумму",
            ]);

            return true;
        }

        if (is_numeric($text)) {
            $amount = floatval($text);
            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "Вы ввели: $amount",
            ]);

            return true;
        }

        return false;
    }
}
