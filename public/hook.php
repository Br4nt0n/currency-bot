<?php

declare(strict_types=1);

use App\Application\Dto\DayRateDto;
use App\Application\Enums\CurrencyPairEnum;
use App\Application\Enums\TradeDirectionEnum;
use App\Application\Handlers\ContainerHelper;
use App\Application\Services\CurrencyServiceInterface;
use App\Application\Services\MongoDbService;
use Psr\Log\LoggerInterface;

require __DIR__ . '/index.php';

ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

/** @var LoggerInterface $log */
$log = ContainerHelper::get(LoggerInterface::class);
$log->info( 'Обновление курсов валют ' . date('Y-m-d H:i:s'));

try {
    /** @var Redis $redis */
    $redis = ContainerHelper::get(Redis::class);
    $redis->flushAll();

    /** @var CurrencyServiceInterface $service */
    $service = ContainerHelper::get(CurrencyServiceInterface::class);
    $service->getDollarBlueRate();
    $log->info('Курс блю доллара обновлен');

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

} catch (Throwable $e) {
    $log->error($e->getMessage());
}
