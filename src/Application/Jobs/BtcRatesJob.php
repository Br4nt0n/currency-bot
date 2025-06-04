<?php

declare(strict_types=1);

namespace App\Application\Jobs;

use App\Application\Enums\CacheKeyEnum;
use App\Application\Enums\CurrencyCodeEnum;
use App\Application\Handlers\ContainerHelper;
use App\Application\Jobs\Traits\RetryableJobTrait;
use App\Application\Log\QueueLoggerInterface;
use App\Application\Repositories\BlockChainRepository;
use Psr\Log\LoggerInterface;
use Redis;
use Resque\Job\Job;
use Throwable;

class BtcRatesJob extends Job
{
    use RetryableJobTrait;

    private LoggerInterface $logger;

    private const int TTL = 15 * 60;

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

            /** @var BlockChainRepository $repository */
            $repository = ContainerHelper::get(BlockChainRepository::class);
            $result = $repository->getBTCCurrency();

            $btcUsd = $result->firstWhere('currency', CurrencyCodeEnum::USD->value)?->last;
            $btcRub = $result->firstWhere('currency', CurrencyCodeEnum::RUB->value)?->last;

            $redis->setex(CacheKeyEnum::BTC->format(CurrencyCodeEnum::USD->value), self::TTL, $btcUsd);
            $redis->setex(CacheKeyEnum::BTC->format(CurrencyCodeEnum::RUB->value), self::TTL, $btcRub);

            $this->logger->info("Job $class completed successfully", $this->args);
            $this->logger->info('Курс BTC обновлен');
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            $this->retryOrFail($e);
        }
    }

}
