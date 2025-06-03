<?php

declare(strict_types=1);

use App\Application\Enums\CurrencyPairEnum;
use App\Application\Handlers\ContainerHelper;
use App\Application\Jobs\BlueDollarJob;
use App\Application\Jobs\QuickChartJob;
use App\Application\Jobs\RubRatesJob;
use App\Application\Jobs\UsdRatesJob;
use App\Application\Jobs\UsdSaveMongoJob;
use App\Application\Services\CurrencyServiceInterface;
use App\Application\Services\QuickChartService;
use Psr\Log\LoggerInterface;
use Resque\Resque;
use Resque\Scheduler;

require __DIR__ . '/../vendor/autoload.php';
$container = require __DIR__ . '/../src/bootstrap/container.php';

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE);

/** @var LoggerInterface $log */
$log = ContainerHelper::get(LoggerInterface::class);
$log->info('Обновление курсов валют ' . date('Y-m-d H:i:s'));

try {
    $queue = getenv('REDIS_QUEUE');
    // подключаем очереди
    ContainerHelper::get(Resque::class);
    $chartKey = QuickChartService::CACHE_KEY;
    $chartKey = sprintf($chartKey, strtolower(CurrencyPairEnum::USD_RUB->value));

    $keys = [
        CurrencyServiceInterface::DOLLAR_BLUE_SELL,
        CurrencyServiceInterface::DOLLAR_BLUE_BUY,
        CurrencyServiceInterface::USD_RUB,
        CurrencyServiceInterface::USD_ARS,
        CurrencyServiceInterface::RUB_ARS,
        CurrencyServiceInterface::RUB_USD,
        CurrencyServiceInterface::USD_ARS_SELL,
        CurrencyServiceInterface::USD_ARS_BUY,
        $chartKey,
    ];
    /** @var Redis $redis */
    $redis = ContainerHelper::get(Redis::class);
    $redis->del($keys);

    // Доллар блю
    Resque::enqueue($queue, BlueDollarJob::class, [
        'timestamp' => time(),
    ]);
    $log->info('Джоб для доллар блю создан');

    // курс доллара к рублю и песо
    Resque::enqueue($queue, UsdRatesJob::class, [
        'timestamp' => time(),
    ]);
    $log->info('Джоб курс доллара добавлен');

    // курс рубля отложен из-за рейтлимита
    Scheduler::enqueueIn(60, $queue, RubRatesJob::class, [
        'timestamp' => time(),
    ]);
    $log->info('Джоб курс рубля добавлен');

    // сохранение в монго дб
    Scheduler::enqueueIn(30, $queue, UsdSaveMongoJob::class, [
        'timestamp' => time(),
    ]);
    $log->info('Джоб mongoDB добавлен');

    // график
    Scheduler::enqueueIn(120, $queue, QuickChartJob::class, [
        'timestamp' => time(),
    ]);
    $log->info('Джоб для графика добавлен');

    $log->info('Обновление курсов валют завершено ' . date('Y-m-d H:i:s'));

} catch (Throwable $e) {
    $log->error($e->getMessage());
}
