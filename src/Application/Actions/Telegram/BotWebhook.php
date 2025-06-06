<?php

declare(strict_types=1);

namespace App\Application\Actions\Telegram;

use App\Application\Actions\Action;
use App\Application\Commands\TelegramCommands\ChartCommand;
use App\Application\Commands\TelegramCommands\ConvertCommand;
use App\Application\Commands\TelegramCommands\LatestRatesCommand;
use App\Application\Commands\TelegramCommands\StartCommand;
use App\Application\Enums\BotCommandEnum;
use App\Application\Factories\CommandsFactoryInterface;
use App\Infrastructure\Services\ConvertStepService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Telegram\Bot\Api;
use Throwable;

class BotWebhook extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private readonly Api $telegram,
        private readonly ConvertStepService $stepService,
        private readonly CommandsFactoryInterface $factory,
    )
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

            $chatId = $update->getChat()->get('id');
            $this->logger->info('Request from chat ID: ' . $chatId);

            $callback = $update->callbackQuery;

            // Обработка шагов после команды
            $this->stepService->handle($this->telegram, $update);

            if ($callback !== null) {
                $data = $callback->get('data');
                $command = $this->factory->create($data);
                $command->make($this->telegram, $update, []);

                $this->respondWithData(['OK']);
            }

            if ($update->getMessage()->has('location')) {
                $command = $this->factory->create(BotCommandEnum::EXCHANGE->value);
                $command->make($this->telegram, $update, []);
            }

        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            return $this->respondWithData(['Что-то пошло не так, мы уже исправляем'], 500);
        }

        return $this->respondWithData(['OK']);
    }

}
