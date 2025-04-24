<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class ConvertCommand extends Command
{
    protected string $name = 'convert';
    protected string $description = 'Convert command';

    public function handle()
    {
        $chatID = $this->getUpdate()->getChat()->get('id');
        $replyMarkup = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button('USD'),
                Keyboard::button('RUB'),
                Keyboard::button('ARS'),
            ]);

        $this->replyWithMessage([
            'text' => 'Выбери валюту, которую хочешь пересчитать' . $chatID,
            'reply_markup' => $replyMarkup,
        ]);
    }

}
