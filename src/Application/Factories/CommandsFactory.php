<?php

declare(strict_types=1);

namespace App\Application\Factories;

use App\Application\Commands\TelegramCommands\ChartCommand;
use App\Application\Commands\TelegramCommands\ConvertCommand;
use App\Application\Commands\TelegramCommands\LatestRatesCommand;
use App\Application\Commands\TelegramCommands\StartCommand;
use InvalidArgumentException;
use Telegram\Bot\Commands\Command;

final class CommandsFactory
{
    public static function factory(string $type): Command
    {
        return match ($type) {
            'start' => new StartCommand(),
            'convert' => new ConvertCommand(),
            'chart' => new ChartCommand(),
            'latest' => new LatestRatesCommand(),
            default => throw new InvalidArgumentException("Undefined $type of command")
        };
    }
}
