<?php

declare(strict_types=1);

namespace App\Application\Jobs;

use App\Application\Handlers\ContainerHelper;
use App\Application\Jobs\Traits\RetryableJobTrait;
use App\Application\Log\QueueLoggerInterface;
use App\Application\Services\CurrencyServiceInterface;
use Psr\Log\LoggerInterface;
use Resque\Job\Job;
use Throwable;

class EurRatesJob extends Job
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
            $this->logger->info("Starting job $class ... ", $this->args);
            /** @var CurrencyServiceInterface $service */
            $service = ContainerHelper::get(CurrencyServiceInterface::class);
            $service->getEurRates();

            $this->logger->info("Job $class completed successfully", $this->args);
            $this->logger->info('Курс EUR обновлен');
        } catch (Throwable $e) {
            $this->retryOrFail($e);
        }
    }

}
