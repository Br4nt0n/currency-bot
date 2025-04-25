<?php

declare(strict_types=1);

namespace App\Application\Actions\Telegram;

use App\Application\Actions\Action;
use App\Application\Commands\TelegramCommands\ConvertCommand;
use App\Application\Commands\TelegramCommands\StartCommand;
use App\Application\Handlers\ConvertStepHandler;
use App\Application\Services\ConvertStepService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Redis;
use Telegram\Bot\Api;
use Throwable;

class BotWebhook extends Action
{
    public function __construct(LoggerInterface $logger, private ConvertStepService $stepService)
    {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        try {
            $telegram = new Api(getenv('BOT_API_KEY'));
            $telegram->addCommands([StartCommand::class, ConvertCommand::class]);
            $telegram->commandsHandler(true);
            $update = $telegram->getWebhookUpdate();
            $callback = $update->callbackQuery;
            // Обработка шагов после команды
            $this->stepService->handle($telegram, $update);

            $this->logger->info(print_r($callback->get('data'), true));
            $this->logger->info(print_r($callback->objectType(), true));
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            echo $e->getMessage();
        }

        return $this->respondWithData(['OK']);
    }

}
