<?php

declare(strict_types=1);

namespace App\Application\Actions\Telegram;

use App\Application\Actions\Action;
use PhpTelegramBot\Core\Exceptions\TelegramException;
use PhpTelegramBot\Core\Telegram;
use Psr\Http\Message\ResponseInterface as Response;

class BotAction extends Action
{

    protected function action(): Response
    {
        echo getenv('BOT_API_KEY') . PHP_EOL . getenv('BOT_NAME') . PHP_EOL . getenv('FREE_CURRENCY_API_KEY');

        echo PHP_EOL . getenv('REDIS_HOST') . PHP_EOL . getenv('REDIS_PORT');

        try {
            // Create Telegram API object
            $telegram = new Telegram(getenv('BOT_API_KEY'), getenv('BOT_NAME'));

            // Set webhook
            $result = $telegram->setWebhook([
                'url' => getenv('APP_NAME') . 'hook.php',
            ]);
            if ($result) {
                echo "OK";
            }
        } catch (TelegramException $e) {
            // log telegram errors
            echo $e->getMessage();
        }

        return $this->response;
    }

}
