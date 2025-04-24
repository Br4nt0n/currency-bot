<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Start Command to get you started';

    public function handle()
    {
        $this->replyWithMessage([
            'text' => 'Hey, there! Welcome to our bot!',
        ]);
    }

}

