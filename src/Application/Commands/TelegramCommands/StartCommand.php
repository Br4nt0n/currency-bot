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
                Keyboard::button('Конвертировать валюту (рубли, доллары, песо)'),
                Keyboard::button('Узнать текущий курс интересующей валюты'),
                Keyboard::button('Посмотреть график интересующей валюты за последнее время'),
            ]);

        $this->replyWithMessage([
            'text' => 'У нас тут доступны такие опции: ',
            'reply_markup' => $replyMarkup
        ]);
    }

}

