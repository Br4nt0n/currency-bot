<?php

declare(strict_types=1);

namespace App\Application\Actions\Telegram;

use App\Application\Actions\Action;
use App\Application\Commands\TelegramCommands\ChartCommand;
use App\Application\Commands\TelegramCommands\ConvertCommand;
use App\Application\Commands\TelegramCommands\LatestRatesCommand;
use App\Application\Commands\TelegramCommands\StartCommand;
use App\Application\Factories\CommandsFactory;
use App\Application\Services\ConvertStepService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Telegram\Bot\Api;
use Throwable;

class BotWebhook extends Action
{
    public function __construct(LoggerInterface $logger, private Api $telegram, private ConvertStepService $stepService)
    {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        try {
            $this->telegram->addCommands([
                StartCommand::class,
                ConvertCommand::class,
                ChartCommand::class,
                LatestRatesCommand::class,
            ]);
            $this->telegram->commandsHandler(true);
            $update = $this->telegram->getWebhookUpdate();
            $callback = $update->callbackQuery;

            // Обработка шагов после команды
            $this->stepService->handle($this->telegram, $update);

            if ($callback !== null) {
                $data = $callback->get('data');
                $command = CommandsFactory::factory($data);
                $command->make($this->telegram, $update, []);
                exit;
            }

        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return $this->respondWithData(['OK']);
    }

}
