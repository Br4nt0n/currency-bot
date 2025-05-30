<?php

declare(strict_types=1);

use App\Application\Handlers\ContainerHelper;
use App\Application\Jobs\BlueDollarJob;
use App\Application\Jobs\QuickChartJob;
use App\Application\Jobs\UsdRatesJob;
use App\Application\Services\CurrencyServiceInterface;
use App\Application\Services\MongoDbService;
use Psr\Log\LoggerInterface;
use Resque\Resque;

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

    $keys = [
        CurrencyServiceInterface::DOLLAR_BLUE_SELL,
        CurrencyServiceInterface::DOLLAR_BLUE_BUY,
        CurrencyServiceInterface::USD_RUB,
        CurrencyServiceInterface::USD_ARS,
        CurrencyServiceInterface::RUB_ARS,
        CurrencyServiceInterface::RUB_USD,
        CurrencyServiceInterface::USD_ARS_SELL,
        CurrencyServiceInterface::USD_ARS_BUY,
    ];
    /** @var Redis $redis */
    $redis = ContainerHelper::get(Redis::class);
    $redis->del($keys);

    // Доллар блю
    Resque::enqueue($queue, BlueDollarJob::class, [
        'timestamp' => time(),
    ]);
    $log->info('Джоб для доллар блю создан');

    // график
    Resque::enqueue($queue, QuickChartJob::class, [
        'timestamp' => time(),
    ]);
    $log->info('Джоб для графика добавлен');

    // курс доллара к рублю и песо
    Resque::enqueue($queue, UsdRatesJob::class, [
        'timestamp' => time(),
    ]);
    $log->info('Джоб курс доллара добавлен');

    sleep(5);

    /** @var CurrencyServiceInterface $service */
    $service = ContainerHelper::get(CurrencyServiceInterface::class);
    $service->getRubRates();
    $log->info('Курс рубля обновлен');

    /** @var MongoDbService $mongoService */
    $mongoService = ContainerHelper::get(MongoDbService::class);
//    $mongoService->saveUsdRate(new DayRateDto(
//        pair: CurrencyPairEnum::USD_RUB,
//        direction: TradeDirectionEnum::BUY,
//        value: $usdDto->usdRub,
//    ));

//    $mongoService->saveUsdRate(new DayRateDto(
//        pair: CurrencyPairEnum::USD_ARS,
//        direction: TradeDirectionEnum::BUY,
//        value: $usdDto->usdArs,
//    ));
//    $log->info('Данные записаны в Mongo DB');
    $log->info('Обновление курсов валют завершено ' . date('Y-m-d H:i:s'));

} catch (Throwable $e) {
    $log->error($e->getMessage());
}
