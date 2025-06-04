<?php

declare(strict_types=1);

namespace App\Application\Factories;

use App\Application\Commands\TelegramCommands\ArsCommand;
use App\Application\Commands\TelegramCommands\BTCRatesCommand;
use App\Application\Commands\TelegramCommands\ChartCommand;
use App\Application\Commands\TelegramCommands\CurrencyChartCommand;
use App\Application\Commands\TelegramCommands\RubCommand;
use App\Application\Commands\TelegramCommands\UsdCommand;
use App\Application\Commands\TelegramCommands\ConvertCommand;
use App\Application\Commands\TelegramCommands\LatestRatesCommand;
use App\Application\Commands\TelegramCommands\StartCommand;
use App\Application\Enums\BotCommandEnum;
use InvalidArgumentException;
use Telegram\Bot\Commands\Command;

final class CommandsFactory implements CommandsFactoryInterface
{
    public function create(string $type): Command
    {
        return match ($type) {
            BotCommandEnum::START->value => new StartCommand(),
            BotCommandEnum::CONVERT->value => new ConvertCommand(),
            BotCommandEnum::CHART->value => new ChartCommand(),
            BotCommandEnum::USD_RUB->value, BotCommandEnum::USD_ARS->value => new CurrencyChartCommand(),
            BotCommandEnum::LATEST->value => new LatestRatesCommand(),
            BotCommandEnum::USD_CHOICE->value => new UsdCommand(),
            BotCommandEnum::RUB_CHOICE->value => new RubCommand(),
            BotCommandEnum::ARS_CHOICE->value => new ArsCommand(),
            BotCommandEnum::BTC->value => new BTCRatesCommand(),
            default => throw new InvalidArgumentException("Undefined $type of command")
        };
    }

}
