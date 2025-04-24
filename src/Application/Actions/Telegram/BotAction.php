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

        return $this->response;
    }

}
