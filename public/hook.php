<?php

declare(strict_types=1);

use PhpTelegramBot\Core\Exceptions\TelegramException;
use PhpTelegramBot\Core\Telegram;

require __DIR__ . '/../vendor/autoload.php';


try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram(getenv('BOT_API_KEY'), getenv('BOT_NAME'));

    // Handle telegram webhook request
    var_dump($telegram->getWebhookInfo());


    $telegram->handle();
} catch (TelegramException $e) {
    // Silence is golden!
    // log telegram errors
     echo $e->getMessage();
}
