<?php

declare(strict_types=1);

namespace App\Application\Jobs\Traits;

use App\Application\Handlers\ContainerHelper;
use App\Application\Log\RetryLoggerInterface;
use Psr\Log\LoggerInterface;
use Resque\Scheduler;
use Throwable;

trait RetryableJobTrait
{
    protected int $maxAttempts = 3;

    protected function retryOrFail(Throwable $e): void
    {
        $attempts = $this->args['attempts'] ?? 0;
        $attempts++;

        /** @var LoggerInterface $logger */
        $logger = ContainerHelper::get(RetryLoggerInterface::class);
        $class = get_class($this);
        $logger->warning("Job $class failed on attempt $attempts: {$e->getMessage()}");

        if ($attempts < $this->maxAttempts) {
            Scheduler::enqueueIn(
                10,
                $this->queue ?? 'default',
                $class,
                array_merge($this->args, ['attempts' => $attempts])
            );
            $logger->info("Job $class re-enqueued for attempt $attempts");
        } else {
            $logger->error("Job $class permanently failed after $attempts attempts", $this->args);
        }

        // Важно пробросить ошибку, чтобы ResqueWorker её зафиксировал
        return;
    }
}
