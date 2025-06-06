<?php

declare(strict_types=1);

namespace Application\Actions;

use App\Application\Actions\Telegram\BotWebhook;
use App\Application\Commands\TelegramCommands\StartCommand;
use App\Application\Factories\CommandsFactory;
use App\Application\Factories\CommandsFactoryInterface;
use App\Application\Services\ConversionInterface;
use App\Infrastructure\Services\ConvertStepService;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\CallbackQuery;
use Telegram\Bot\Objects\Update;
use Tests\TestCase;

class BotWebhookActionTest extends TestCase
{
    private LoggerInterface|MockObject $logger;

    private MockObject|Api $api;

    private ConvertStepService $service;

    protected function setUp(): void
    {
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE & ~E_WARNING);
        parent::setUp();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->api = $this->getMockBuilder(Api::class)->disableOriginalConstructor()->getMock();

        $conversionService = $this->getMockBuilder(ConversionInterface::class)->getMock();
        $redis = $this->getMockBuilder(\Redis::class)->getMock();
        $redis->method('exists')->willReturn(false);
        $this->service = new ConvertStepService($conversionService, $redis);
    }

    public function testActionSuccess(): void
    {
        $request = $this->createRequest(
            'POST',
            '/telegram/webhook',
        );

        $this->logger->expects(self::once())->method('info');

        $collection = collect(['id' => 111]);
        $update = $this->getMockBuilder(Update::class)->disableOriginalConstructor()->getMock();
        $update->method('__get')->willReturn(new CallbackQuery(['data' => 'start']));
        $update->expects(self::exactly(2))->method('getChat')->willReturn($collection);

        $command = $this->getMockBuilder(StartCommand::class)->disableOriginalConstructor()->getMock();
        $command->expects(self::once())->method('make');

        $factory = $this->getMockForAbstractClass(CommandsFactoryInterface::class);
        $factory->expects(self::once())->method('create')->willReturn($command);

        $this->api->expects(self::once())->method('commandsHandler');
        $this->api->expects(self::once())->method('getWebhookUpdate')->willReturn($update);

        $action = new BotWebhook(
            $this->logger,
            $this->api,
            $this->service,
            $factory
        );

        $response = $action($request, new Response(), []);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testActionFail(): void
    {
        $request = $this->createRequest(
            'POST',
            '/telegram/webhook',
        );

        $this->logger->expects(self::once())->method('info');
        $this->logger->expects(self::once())->method('error')->with("Undefined unknown of command");

        $collection = collect(['id' => 111]);
        $update = $this->getMockBuilder(Update::class)->disableOriginalConstructor()->getMock();
        $update->method('__get')->willReturn(new CallbackQuery(['data' => 'unknown']));
        $update->expects(self::exactly(2))->method('getChat')->willReturn($collection);

        $this->api->expects(self::once())->method('commandsHandler');
        $this->api->expects(self::once())->method('getWebhookUpdate')->willReturn($update);

        // выкинет исключение, команда неизвестна
        $action = new BotWebhook(
            $this->logger,
            $this->api,
            $this->service,
            new CommandsFactory()
        );

        $response = $action($request, new Response(), []);
        $this->assertEquals(500, $response->getStatusCode());
    }

}
