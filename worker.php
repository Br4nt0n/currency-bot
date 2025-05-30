<?php

use App\Application\Handlers\ContainerHelper;
use App\Application\Log\QueueLoggerInterface;
use Psr\Log\LoggerInterface;
use Resque\JobHandler;
use Resque\Resque;
use Resque\Worker\ResqueWorker;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE & ~E_WARNING);
require __DIR__ . '/vendor/autoload.php';

$container = require __DIR__ . '/src/bootstrap/container.php';
ContainerHelper::get(Resque::class);

/** @var LoggerInterface $log */
$log = ContainerHelper::get(QueueLoggerInterface::class);

$worker = new ResqueWorker(getenv('REDIS_QUEUE'));
$worker->setLogger($log);

// Пытается взять одну задачу и обработать
/** @var JobHandler $job */
while ($job = $worker->reserve()) {
    $job->perform($worker);
}
$log->info("No job found — exiting.\n");
exit(0);
