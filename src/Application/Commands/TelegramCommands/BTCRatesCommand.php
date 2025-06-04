<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\BotCommandEnum;
use App\Application\Enums\CacheKeyEnum;
use App\Application\Enums\CurrencyCodeEnum;
use App\Application\Handlers\ContainerHelper;
use Redis;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class BTCRatesCommand extends Command
{
    protected string $name = BotCommandEnum::BTC->value;

    public function handle(): void
    {
        $replyMarkup = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->inline()
            ->row([
                Keyboard::inlineButton(['text' =>'Назад', 'callback_data' => BotCommandEnum::START]),
            ]);

        $text = $this->formRates();

        $this->replyWithMessage([
            'text' => $text,
            'reply_markup' => $replyMarkup,
        ]);
    }

    private function formRates(): string
    {
        /** @var Redis $redis */
        $redis = ContainerHelper::get(Redis::class);
        $date = date('d.m.Y');

        $usdCacheKey = CacheKeyEnum::BTC->format(CurrencyCodeEnum::USD->value);
        $rubCacheKey = CacheKeyEnum::BTC->format(CurrencyCodeEnum::RUB->value);

        if ($redis->exists($rubCacheKey, $usdCacheKey) !== 2) {
            return "На данный момент свежих данных о курсе нет";
        }

        $btcUsd = $redis->get($usdCacheKey);
        $btcRub = $redis->get($rubCacheKey);

        return "На $date ₿ составляет:
                $ $btcUsd Долларов
                    
                ₽ $btcRub Рублей
        ";
    }
}
