<?php

declare(strict_types=1);

namespace App\Application\Actions\Telegram;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;

use Psr\Log\LoggerInterface;
use Telegram\Bot\Api;

class BotAction extends Action
{
    public function __construct(LoggerInterface $logger, private Api $bot)
    {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        try {
            // Example usage
            var_dump($this->bot->getWebhookInfo());
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            echo $e->getMessage();
        }

        return $this->response;
    }

}
