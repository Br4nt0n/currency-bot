<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\BotCommandEnum;
use App\Application\Enums\CurrencyCodeEnum;

class UsdCommand extends BaseCurrencyCommand
{
    protected string $name = BotCommandEnum::USD_CHOICE->value;

    public function handle(): void
    {
        $this->setCurrencyCache(CurrencyCodeEnum::USD);
        $text = CurrencyCodeEnum::USD->value;

        $this->replyWithMessage([
            'text' => "Вы выбрали: $text Введите требуемую сумму",
        ]);
    }

}
