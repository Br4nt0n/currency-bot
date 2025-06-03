<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\BotCommandEnum;
use App\Application\Enums\CurrencyPairEnum;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class ChartCommand extends Command
{
    protected string $name = BotCommandEnum::CHART->value;

    public function handle(): void
    {
        $usdRub = CurrencyPairEnum::USD_RUB->value;
        $usdArs = CurrencyPairEnum::USD_ARS->value;

        $replyMarkup = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->inline()
            ->row([
                Keyboard::inlineButton(['text' => "🇺🇸 -> 🇷🇺 График $usdRub", 'callback_data' => BotCommandEnum::USD_RUB->value]),
            ])
            ->row([
                Keyboard::inlineButton(['text' => "🇺🇸  -> 🇦🇷 График $usdArs", 'callback_data' => BotCommandEnum::USD_ARS->value]),
            ])
            ->row([
                Keyboard::inlineButton(['text' => "В начало", 'callback_data' => BotCommandEnum::START->value]),
            ]);

        $this->replyWithMessage([
            'text' => 'Доступны следующие графики валют:',
            'reply_markup' => $replyMarkup,
        ]);
    }
}

