<?php

declare(strict_types=1);

use App\Application\Handlers\ContainerHelper;
use App\Application\Services\CurrencyServiceInterface;
use Psr\Log\LoggerInterface;

require __DIR__ . '/index.php';

/** @var LoggerInterface $log */
$log = ContainerHelper::get(LoggerInterface::class);

try {
    /** @var Redis $redis */
    $redis = ContainerHelper::get(Redis::class);
    $redis->flushAll();

    /** @var CurrencyServiceInterface $service */
    $service = ContainerHelper::get(CurrencyServiceInterface::class);
    $service->getDollarBlueRate();
    $service->getUsdRates();
    sleep(5);
    $service->getRubRates();
    $log->info('Курсы валют обновлены ' . date('Y-m-d H:i:s'));

} catch (Throwable $e) {
    $log->error($e->getMessage());
}
