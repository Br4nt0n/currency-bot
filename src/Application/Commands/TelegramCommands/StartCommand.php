<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\BotCommandEnum;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class StartCommand extends Command
{
    protected string $name = BotCommandEnum::START->value;
    protected string $description = 'Start Command to get you started';

    public function handle(): void
    {
        $this->replyWithMessage([
            'text' => 'Привет 👋 ! Это бот конвертации валюты!',
        ]);

        $replyMarkup = Keyboard::make()
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::inlineButton([
                    'text' => '💱 Конвертировать',
                    'callback_data' => BotCommandEnum::CONVERT,
                ]),
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '💵 Текущий курс',
                    'callback_data' => BotCommandEnum::LATEST,
                ]),
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '📈 График курса',
                    'callback_data' => BotCommandEnum::CHART,
                ]),
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '₿ Курс BTC',
                    'callback_data' => BotCommandEnum::BTC,
                ]),
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '💸 Ближайшие криптообменники',
                    'callback_data' => BotCommandEnum::LOCATION,
                ]),
            ]);

        $this->replyWithMessage([
            'text' => 'Доступные валюты: 🇦🇷 песо (ARS), 🇺🇸 доллар (USD), 🇷🇺 рубль (RUB). Опции: ',
            'reply_markup' => $replyMarkup
        ]);
    }

}

