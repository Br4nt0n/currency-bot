<?php

declare(strict_types=1);

namespace App\Application\Jobs;

use App\Application\Handlers\ContainerHelper;
use App\Application\Jobs\Traits\RetryableJobTrait;
use Psr\Log\LoggerInterface;
use Resque\Job\Job;
use Throwable;

class ExampleJob extends Job
{
    use RetryableJobTrait;

    private LoggerInterface $logger;

    public function setUp(): void
    {
        parent::setUp();
        require __DIR__ . '/../../bootstrap/container.php';
        $this->logger = ContainerHelper::get('queue_logger');
    }

    public function perform(): void
    {
        try {
            $this->logger->info("Starting job...", $this->args);
//            throw new \RuntimeException('On purpose failure');
            echo json_encode($this->args);
            $this->logger->info("Job completed successfully", $this->args);

        } catch (Throwable $e) {
            $this->retryOrFail($e);
        }

    }
}
