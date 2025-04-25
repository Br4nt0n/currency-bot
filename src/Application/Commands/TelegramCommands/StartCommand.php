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
            ->inline()
            ->row([
                Keyboard::inlineButton(['text' =>'Конвертировать', 'callback_data' => 'convert']),
                Keyboard::inlineButton(['text' =>'Узнать текущий курс', 'callback_data' => 'latest']),
                Keyboard::inlineButton(['text' =>'График валют за последнее время', 'callback_data' => 'chart']),
            ]);

        $this->replyWithMessage([
            'text' => 'Доступные валюты: песо (ARS), доллар (USD), рубль (RUB). Опции: ',
            'reply_markup' => $replyMarkup
        ]);
    }

}

