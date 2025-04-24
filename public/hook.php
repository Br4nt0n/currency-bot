<?php

declare(strict_types=1);

use Telegram\Bot\Api;

require __DIR__ . '/../vendor/autoload.php';


try {
    $telegram = new Api(getenv('BOT_API_KEY'));

    $telegram->addCommand(\App\Application\Commands\TelegramCommands\StartCommand::class);
    $update = $telegram->commandsHandler(true);
    var_dump($update);
} catch (Throwable $e) {
    // Silence is golden!
    // log telegram errors
     echo $e->getMessage();
}
