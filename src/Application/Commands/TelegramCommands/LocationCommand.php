<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\BotCommandEnum;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class LocationCommand extends Command
{
    protected string $description = BotCommandEnum::LOCATION->value;

    public function handle(): void
    {
        $replyMarkup = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button([
                    'text' => '📍 Отправить местоположение',
                    'request_location' => true,
                ]),
            ]);

        $this->replyWithMessage([
            'text' => 'Пожалуйста, отправьте своё местоположение 📍, чтобы я мог найти ближайшие обменные пункты.',
            'reply_markup' => $replyMarkup
        ]);
    }

}
