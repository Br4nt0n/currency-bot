<?php

declare(strict_types=1);

namespace App\Application\Factories;

use App\Application\Commands\TelegramCommands\ArsCommand;
use App\Application\Commands\TelegramCommands\ChartCommand;
use App\Application\Commands\TelegramCommands\RubCommand;
use App\Application\Commands\TelegramCommands\UsdCommand;
use App\Application\Commands\TelegramCommands\ConvertCommand;
use App\Application\Commands\TelegramCommands\LatestRatesCommand;
use App\Application\Commands\TelegramCommands\StartCommand;
use App\Application\Enums\BotCommandEnum;
use InvalidArgumentException;
use Telegram\Bot\Commands\Command;

final class CommandsFactory
{
    public static function factory(string $type): Command
    {
        return match ($type) {
            BotCommandEnum::START->value => new StartCommand(),
            BotCommandEnum::CONVERT->value => new ConvertCommand(),
            BotCommandEnum::CHART->value => new ChartCommand(),
            BotCommandEnum::LATEST->value => new LatestRatesCommand(),
            BotCommandEnum::USD_CHOICE->value => new UsdCommand(),
            BotCommandEnum::RUB_CHOICE->value => new RubCommand(),
            BotCommandEnum::ARS_CHOICE->value => new ArsCommand(),
            default => throw new InvalidArgumentException("Undefined $type of command")
        };
    }

}
