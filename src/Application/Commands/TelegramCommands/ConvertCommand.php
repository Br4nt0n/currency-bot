<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\CurrencyCodeEnum;
use Psr\Log\LoggerInterface;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class ConvertCommand extends Command
{
    protected string $name = 'convert';
    protected string $description = 'Convert command';

    public function handle()
    {
        $replyMarkup = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(CurrencyCodeEnum::USD->value),
                Keyboard::button(CurrencyCodeEnum::RUB->value),
                Keyboard::button(CurrencyCodeEnum::ARS->value),
                Keyboard::button('назад'),
            ]);

        $this->replyWithMessage([
            'text' => 'Выбери валюту, которую хочешь пересчитать',
            'reply_markup' => $replyMarkup,
        ]);
    }

}
