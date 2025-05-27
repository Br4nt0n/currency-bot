<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\BotCommandEnum;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class ConvertCommand extends Command
{
    protected string $name = 'convert';

    public function handle(): void
    {
        $replyMarkup = Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '🇺🇸 USD',
                    'callback_data' => BotCommandEnum::USD_CHOICE,
                ]),
                Keyboard::inlineButton([
                    'text' => '🇷🇺 RUB',
                    'callback_data' => BotCommandEnum::RUB_CHOICE,
                ]),
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '🇦🇷 ARS',
                    'callback_data' => BotCommandEnum::ARS_CHOICE,
                ]),
                Keyboard::inlineButton([
                    'text' => 'Назад',
                    'callback_data' => BotCommandEnum::START,
                ]),
            ]);

        $this->replyWithMessage([
            'text' => 'Выбери валюту, которую хочешь пересчитать',
            'reply_markup' => $replyMarkup,
        ]);
    }

}
