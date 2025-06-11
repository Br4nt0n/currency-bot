<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\BotCommandEnum;
use App\Application\Enums\CurrencyCodeEnum;

class EurCommand extends BaseCurrencyCommand
{
    protected string $name = BotCommandEnum::USD_CHOICE->value;

    public function handle(): void
    {
        $this->setCurrencyCache(CurrencyCodeEnum::EUR);
        $text = CurrencyCodeEnum::EUR->value;

        $this->replyWithMessage([
            'text' => "Вы выбрали: $text Введите требуемую сумму",
        ]);
    }

}
