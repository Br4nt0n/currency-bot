<?php

declare(strict_types=1);

use App\Application\Commands\TelegramCommands\ConvertCommand;
use App\Application\Commands\TelegramCommands\StartCommand;
use App\Application\Handlers\ConvertStepHandler;
use DI\Container;
use Slim\Factory\AppFactory;
use Telegram\Bot\Api;

require __DIR__ . '/../vendor/autoload.php';


try {
    $telegram = new Api(getenv('BOT_API_KEY'));

    $telegram->addCommands([StartCommand::class, ConvertCommand::class]);
    $telegram->commandsHandler(true);
    $update = $telegram->getWebhookUpdate();
    // Обработка шагов после команды
    $app = AppFactory::create();
    $redis = $app->getContainer()->get(Redis::class);
    ConvertStepHandler::handle($telegram, $update, $redis);

//    $update->callbackQuery->get('data');
} catch (Throwable $e) {
    // Silence is golden!
    file_put_contents('error.log', $e->getMessage());
     echo $e->getMessage();
}
