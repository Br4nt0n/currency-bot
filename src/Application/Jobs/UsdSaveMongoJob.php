<?php

declare(strict_types=1);

namespace App\Application\Jobs;

use App\Application\Dto\DayRateDto;
use App\Application\Enums\CurrencyPairEnum;
use App\Application\Enums\TradeDirectionEnum;
use App\Application\Handlers\ContainerHelper;
use App\Application\Jobs\Traits\RetryableJobTrait;
use App\Application\Log\QueueLoggerInterface;
use App\Application\Repositories\MongoUsdRepository;
use App\Application\Services\CurrencyServiceInterface;
use Psr\Log\LoggerInterface;
use Redis;
use Resque\Exceptions\RedisException;
use Resque\Job\Job;
use Throwable;

class UsdSaveMongoJob extends Job
{
    use RetryableJobTrait;

    private LoggerInterface $logger;

    public function setUp(): void
    {
        parent::setUp();
        require __DIR__ . '/../../bootstrap/container.php';
        $this->logger = ContainerHelper::get(QueueLoggerInterface::class);
    }

    public function perform(): void
    {
        try {
            $class = get_class($this);
            $this->logger->info("Starting job $class ...", $this->args);

            /** @var Redis $redis */
            $redis = ContainerHelper::get(Redis::class);
            $usdRub = $redis->get(CurrencyServiceInterface::USD_RUB);
            $usdArs = $redis->get(CurrencyServiceInterface::USD_ARS);

            if ($usdRub === false) {
                throw new RedisException(CurrencyServiceInterface::USD_RUB . " value no presents!");
            }

            if ($usdArs === false) {
                throw new RedisException(CurrencyServiceInterface::USD_ARS . " value no presents!");
            }

            /** @var MongoUsdRepository $mongoRepository */
            $mongoRepository = ContainerHelper::get(MongoUsdRepository::class);
            $mongoRepository->saveDayRate(new DayRateDto(
                pair: CurrencyPairEnum::USD_RUB,
                direction: TradeDirectionEnum::BUY,
                value: (float)$usdRub,
            ));

            $mongoRepository->saveDayRate(new DayRateDto(
                pair: CurrencyPairEnum::USD_ARS,
                direction: TradeDirectionEnum::BUY,
                value: (float)$usdArs,
            ));

            $this->logger->info('Данные USD записаны в Mongo DB');
        } catch (Throwable $e) {
            $this->retryOrFail($e);
        }
    }

}
