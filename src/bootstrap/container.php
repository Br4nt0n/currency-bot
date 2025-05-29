<?php

use App\Application\Handlers\ContainerHelper;
use DI\Container;
use DI\ContainerBuilder;
use Dotenv\Dotenv;

// Загружаем .env (если ещё не загружен)
$dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../../');
$dotenv->load();

$containerBuilder = new ContainerBuilder();

// Создаём DI контейнер
$container = new Container();

// Set up settings
$settings = require __DIR__ . '/../../app/settings.php';
$settings($containerBuilder);

// Подключаем зависимости
$dependencies = require __DIR__ . '/../../app/dependencies.php';
$dependencies($containerBuilder);

$container = $containerBuilder->build();
ContainerHelper::setContainer($container);

return $container;

