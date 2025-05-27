<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\BotCommandEnum;
use App\Application\Enums\CurrencyCodeEnum;

class ArsCommand extends BaseCurrencyCommand
{
    protected string $name = BotCommandEnum::ARS_CHOICE->value;

    public function handle(): void
    {
        $this->setCurrencyCache(CurrencyCodeEnum::ARS);
        $text = CurrencyCodeEnum::ARS->value;

        $this->replyWithMessage([
            'text' => "Вы выбрали: $text Введите требуемую сумму",
        ]);
    }

}
