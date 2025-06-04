<?php

declare(strict_types=1);

use App\Application\Clients\BlockchainClient;
use App\Application\Clients\BlueLyticsClient;
use App\Application\Clients\ExchangeRateClient;
use App\Application\Factories\CommandsFactory;
use App\Application\Factories\CommandsFactoryInterface;
use App\Application\Log\QueueLoggerInterface;
use App\Application\Log\RetryLoggerInterface;
use App\Application\Repositories\BlueLyticsRepository;
use App\Application\Repositories\ExchangeRateRepository;
use App\Application\Repositories\MongoUsdRepository;
use App\Application\Services\ConversionInterface;
use App\Application\Services\ConversionService;
use App\Application\Services\CurrencyService;
use App\Application\Services\CurrencyServiceInterface;
use App\Application\Services\MongoDbService;
use App\Application\Services\QuickChartService;
use App\Application\Settings\SettingsInterface;
use App\Application\Storages\MongoUsdStorage;
use DI\ContainerBuilder;
use MongoDB\Client;
use MongoDB\Client as MongoDbClient;
use MongoDB\Driver\ServerApi;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Resque\Resque;
use Telegram\Bot\Api;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        QueueLoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('currency-queue');
            $logger = new Logger($loggerSettings['name']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($loggerSettings['path'], $loggerSettings['level']));

            return $logger;
        },

        RetryLoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('retry-queue');
            $logger = new Logger($loggerSettings['name']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($loggerSettings['path'], $loggerSettings['level']));

            return $logger;
        },

        Redis::class => function () {
            return new Redis([
                'host' => getenv('REDIS_HOST'),
                'port' => (int)getenv('REDIS_PORT'),
                'auth' => getenv('REDIS_PASSWORD'),
            ]);
        },

        ExchangeRateClient::class => function (ContainerInterface $c) {
            return new ExchangeRateClient(
                $c->get(LoggerInterface::class),
                new \GuzzleHttp\Client(),
                getenv('EXCHANGE_URL'),
                getenv('FREE_CURRENCY_API_KEY'),
            );
        },

        BlueLyticsClient::class => function (ContainerInterface $c) {
            return new BlueLyticsClient(
                $c->get(LoggerInterface::class),
                new \GuzzleHttp\Client(),
                getenv('DOLLAR_BLUE_URI'),
            );
        },

        BlockchainClient::class => function (ContainerInterface $c) {
            return new BlockchainClient(
                $c->get(LoggerInterface::class),
                new \GuzzleHttp\Client(),
                getenv('BLOCKCHAIN_URI'),
            );
        },

        MongoDbClient::class => function () {
            $apiVersion = new ServerApi((string)ServerApi::V1);
            $user = getenv('MONGO_USERNAME');
            $pass = getenv('MONGO_PASSWORD');
            $server = getenv('MONGO_URI');
            return new MongoDbClient(
                uri: sprintf($server, $user, $pass),
                driverOptions: ['serverApi' => $apiVersion]
            );
        },

        CurrencyServiceInterface::class => function (ContainerInterface $c) {
            return new CurrencyService(
                $c->get(ExchangeRateRepository::class),
                $c->get(BlueLyticsRepository::class),
                $c->get(Redis::class),
            );
        },

        ConversionInterface::class => function (ContainerInterface $c) {
            return new ConversionService(
                $c->get(Redis::class),
                $c->get(CurrencyServiceInterface::class),
            );
        },

        MongoUsdRepository::class => function (ContainerInterface $c) {
            $client = $c->get(Client::class);
            $storage = new MongoUsdStorage($client);
            return new MongoUsdRepository($storage);
        },

        MongoDbService::class => function (ContainerInterface $c) {
            $repository = $c->get(MongoUsdRepository::class);
            return new MongoDbService($repository);
        },

        QuickChartService::class => function (ContainerInterface $c) {
            return new QuickChartService(
                $c->get(Redis::class),
                new QuickChart(),
            );
        },

        Api::class => function () {
            $telegram = new Api(getenv('BOT_API_KEY'));
            $telegram->setWebhook([
                'url' => getenv('APP_NAME') . 'telegram/webhook',
            ]);

            return $telegram;
        },

        Resque::class => function () {
            $redisDsn = sprintf(
                'redis://:%s@%s:%s',
                getenv('REDIS_PASSWORD') ?: '',
                getenv('REDIS_HOST') ?: 'redis_container',
                getenv('REDIS_PORT') ?: 6379
            );

            Resque::setBackend($redisDsn);
            return new Resque();
        },

        CommandsFactoryInterface::class => function () {
            return new CommandsFactory();
        },

    ]);
};
