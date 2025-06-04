<?php

declare(strict_types=1);

use App\Application\Handlers\ContainerHelper;
use App\Application\Jobs\BtcRatesJob;
use Psr\Log\LoggerInterface;
use Resque\Resque;

require __DIR__ . '/vendor/autoload.php';
$container = require __DIR__ . '/src/bootstrap/container.php';

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE & ~E_WARNING);

/** @var LoggerInterface $log */
$log = ContainerHelper::get(LoggerInterface::class);
$log->info('Обновление курса биткоина');

try {
    $queue = getenv('REDIS_QUEUE');
    // подключаем очереди
    ContainerHelper::get(Resque::class);

    /** @var Redis $redis */
    $redis = ContainerHelper::get(Redis::class);

    Resque::enqueue($queue, BtcRatesJob::class, [
        'timestamp' => time(),
    ]);
    $log->info('Джоб для BTC создан');

} catch (Throwable $exception) {
    $log->error($exception->getMessage());
}
