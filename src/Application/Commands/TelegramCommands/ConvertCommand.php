<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use Telegram\Bot\Commands\Command;

class ConvertCommand extends Command
{
    protected string $name = 'convert';
    protected string $description = 'Convert command';

    public function handle()
    {
        $this->replyWithMessage([
            'text' => 'Выбери валюту, которую хочешь пересчитать',
        ]);
    }

}
