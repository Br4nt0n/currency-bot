<?php

declare(strict_types=1);

namespace App\Application\Jobs\Traits;

use App\Application\Handlers\ContainerHelper;
use Psr\Log\LoggerInterface;
use Resque\Resque;
use Throwable;

trait RetryableJobTrait
{
    protected int $maxAttempts = 3;

    protected function retryOrFail(Throwable $e): void
    {
        $attempts = $this->args['attempts'] ?? 0;
        $attempts++;

        /** @var LoggerInterface $logger */
        $logger = ContainerHelper::get('retry_logger');

        $logger->warning("Job failed on attempt $attempts: {$e->getMessage()}");

        if ($attempts < $this->maxAttempts) {
            Resque::enqueue(
                $this->queue ?? 'default',
                get_class($this),
                array_merge($this->args, ['attempts' => $attempts])
            );
            $logger->info("Job re-enqueued for attempt $attempts");
        } else {
            $logger->error("Job permanently failed after $attempts attempts", $this->args);
        }

        // Важно пробросить ошибку, чтобы ResqueWorker её зафиксировал
        return;
    }
}
