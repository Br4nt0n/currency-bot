<?php

declare(strict_types=1);

namespace App\Application\Actions\Telegram;

use App\Application\Actions\Action;
use PhpTelegramBot\Core\Exceptions\TelegramException;
use PhpTelegramBot\Core\Telegram;
use Psr\Http\Message\ResponseInterface as Response;

use Telegram\Bot\Api;

class BotAction extends Action
{
    protected function action(): Response
    {

        try {
            $telegram = new Api(getenv('BOT_API_KEY'));
            $result = $telegram->setWebhook([
                'url' => getenv('APP_NAME') . 'hook.php',
            ]);

            // Example usage
            var_dump($result, $telegram->getWebhookInfo());
        } catch (\Throwable $e) {
            // log telegram errors
            echo $e->getMessage();
        }

        return $this->response;
    }

}
