<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true,
                'logError'            => true,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => (bool)getenv('docker') !== false ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'currency-queue' => [
                    'name' => 'slim-queue',
                    'path' => (bool)getenv('docker') !== false ? 'php://stdout' : __DIR__ . '/../logs/queue.log',
                    'level' => Logger::INFO,
                ],
                'retry-queue' => [
                    'name' => 'slim-retry-queue',
                    'path' => (bool)getenv('docker') !== false ? 'php://stdout' : __DIR__ . '/../logs/retry-queue.log',
                    'level' => Logger::INFO,
                ],
            ]);
        }
    ]);
};
