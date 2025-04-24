<?php

declare(strict_types=1);

use App\Application\Commands\TelegramCommands\ConvertCommand;
use App\Application\Commands\TelegramCommands\StartCommand;
use App\Application\Handlers\ConvertStepHandler;
use Telegram\Bot\Api;

require __DIR__ . '/../vendor/autoload.php';


try {
    $telegram = new Api(getenv('BOT_API_KEY'));

    $telegram->addCommands([StartCommand::class, ConvertCommand::class]);
    $telegram->commandsHandler(true);
    $update = $telegram->getWebhookUpdate();
    // Обработка шагов после команды
//    ConvertStepHandler::handle($telegram, $update);
} catch (Throwable $e) {
    // Silence is golden!
    // log telegram errors
     echo $e->getMessage();
}
