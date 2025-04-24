<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Start Command to get you started';

    public function handle()
    {
        $this->replyWithMessage([
            'text' => 'Привет! Добро пожаловать в бот конвертации валюты!',
        ]);

        $replyMarkup = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::inlineButton(['text' =>'Конвертировать', 'callback_data' => '/convert']),
                Keyboard::button('Узнать текущий курс'),
                Keyboard::button('Посмотреть график за последнее время'),
            ]);

        $this->replyWithMessage([
            'text' => 'Доступные валюты: песо (ARS), доллар (USD), рубль (RUB). Опции: ',
            'reply_markup' => $replyMarkup
        ]);
    }

}

