<?php

use App\Application\Handlers\ContainerHelper;
use App\Application\Log\QueueLoggerInterface;
use Psr\Log\LoggerInterface;
use Resque\Resque;

require __DIR__ . '/../../vendor/autoload.php';

$container = require __DIR__ . '/container.php';

ContainerHelper::get(Resque::class);

/** @var LoggerInterface $log */
$log = ContainerHelper::get(QueueLoggerInterface::class);

$log->info('Resque scheduler started');
