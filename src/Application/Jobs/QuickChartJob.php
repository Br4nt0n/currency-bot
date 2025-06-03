<?php

declare(strict_types=1);

namespace App\Application\Jobs;

use App\Application\Enums\CurrencyPairEnum;
use App\Application\Handlers\ContainerHelper;
use App\Application\Jobs\Traits\RetryableJobTrait;
use App\Application\Log\QueueLoggerInterface;
use App\Application\Services\MongoDbService;
use App\Application\Services\QuickChartService;
use Psr\Log\LoggerInterface;
use Resque\Job\Job;
use Throwable;

class QuickChartJob extends Job
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
            $this->logger->info("Starting job...", $this->args);
            /** @var MongoDbService $mongoDbService */
            $mongoDbService = ContainerHelper::get(MongoDbService::class);
            /** @var QuickChartService $quickChartService */
            $quickChartService = ContainerHelper::get(QuickChartService::class);

            $data = $mongoDbService->getCurrencyPairChartValues(CurrencyPairEnum::USD_RUB);
            $quickChartService->makeChart($data['dates'], $data['values'], CurrencyPairEnum::USD_RUB);
            $this->logger->info('График для usd_rub составлен');

            $data = $mongoDbService->getCurrencyPairChartValues(CurrencyPairEnum::USD_ARS);
            $quickChartService->makeChart($data['dates'], $data['values'], CurrencyPairEnum::USD_ARS);
            $this->logger->info('График для usd_ars составлен');

            $class = get_class($this);
            $this->logger->info("Job $class completed successfully", $this->args);
            $this->logger->info('График для usd_rub составлен');
        } catch (Throwable $e) {
            $this->retryOrFail($e);
        }
    }

}
