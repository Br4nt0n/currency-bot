<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\BotCommandEnum;
use App\Application\Enums\CurrencyCodeEnum;

class RubCommand extends BaseCurrencyCommand
{
    protected string $name = BotCommandEnum::RUB_CHOICE->value;

    public function handle(): void
    {
        $this->setCurrencyCache(CurrencyCodeEnum::RUB);
        $text = CurrencyCodeEnum::RUB->value;

        $this->replyWithMessage([
            'text' => "Вы выбрали: $text Введите требуемую сумму",
        ]);
    }

}
