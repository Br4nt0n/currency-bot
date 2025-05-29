<?php

declare(strict_types=1);

use App\Application\Dto\DayRateDto;
use App\Application\Enums\CurrencyPairEnum;
use App\Application\Enums\TradeDirectionEnum;
use App\Application\Handlers\ContainerHelper;
use App\Application\Jobs\BlueDollarJob;
use App\Application\Services\CurrencyServiceInterface;
use App\Application\Services\MongoDbService;
use App\Application\Services\QuickChartService;
use Psr\Log\LoggerInterface;
use Resque\Resque;

require __DIR__ . '/../vendor/autoload.php';
$container = require __DIR__ . '/../src/bootstrap/container.php';

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE);

// подключаем очереди
ContainerHelper::get(Resque::class);

/** @var LoggerInterface $log */
$log = ContainerHelper::get(LoggerInterface::class);
$log->info('Обновление курсов валют ' . date('Y-m-d H:i:s'));

try {
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

    Resque::enqueue('default', BlueDollarJob::class, [
        'timestamp' => time(),
    ]);
    $log->info('Джоб для доллар блю создан');

    /** @var CurrencyServiceInterface $service */
    $service = ContainerHelper::get(CurrencyServiceInterface::class);

    $usdDto = $service->getUsdRates();
    $log->info('Курс доллара обновлен');

    sleep(5);
    $service->getRubRates();
    $log->info('Курс рубля обновлен');
    $log->info('Курсы валют обновлены ' . date('Y-m-d H:i:s'));

    /** @var MongoDbService $mongoService */
    $mongoService = ContainerHelper::get(MongoDbService::class);
    $mongoService->saveUsdRate(new DayRateDto(
        pair: CurrencyPairEnum::USD_RUB,
        direction: TradeDirectionEnum::BUY,
        value: $usdDto->usdRub,
    ));

    $mongoService->saveUsdRate(new DayRateDto(
        pair: CurrencyPairEnum::USD_ARS,
        direction: TradeDirectionEnum::BUY,
        value: $usdDto->usdArs,
    ));
    $log->info('Данные записаны в Mongo DB');

    /** @var QuickChartService $quickChartService */
    $quickChartService = ContainerHelper::get(QuickChartService::class);
    $data = $mongoService->getCurrencyPairChartValues(CurrencyPairEnum::USD_RUB);
    $quickChartService->makeChart($data['dates'], $data['values'], CurrencyPairEnum::USD_RUB);
    $log->info('График для доллара составлен');

} catch (Throwable $e) {
    $log->error($e->getMessage());
}
