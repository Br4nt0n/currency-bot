<?php

declare(strict_types=1);

namespace App\Application\Factories;

use Telegram\Bot\Commands\Command;

interface CommandsFactoryInterface
{
    public function create(string $type): Command;
}
