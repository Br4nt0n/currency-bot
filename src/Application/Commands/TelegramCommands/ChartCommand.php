<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class ChartCommand extends Command
{
    protected string $name = 'chart';
    protected string $description = '';

    public function handle()
    {
        $replyMarkup = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->inline()
            ->row([
                Keyboard::inlineButton(['text' =>'Назад', 'callback_data' => 'start']),
            ]);

        $this->replyWithMessage([
            'text' => 'Здесь пока ничего нет, но скоро появится!',
            'reply_markup' => $replyMarkup,
        ]);
    }

}

