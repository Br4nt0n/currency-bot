<?php

declare(strict_types=1);

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
use DI\ContainerBuilder;
use MongoDB\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

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
            return new MongoUsdRepository($client);
        },

        MongoDbService::class => function (ContainerInterface $c) {
            $repository = $c->get(MongoUsdRepository::class);
            return new MongoDbService($repository);
        },

        QuickChartService::class => function (ContainerInterface $c) {
            return new QuickChartService(
                $c->get(Redis::class),
            );
        },
    ]);
};
