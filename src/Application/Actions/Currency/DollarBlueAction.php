<?php

declare(strict_types=1);

namespace App\Application\Actions\Currency;

use App\Application\Actions\Action;
use App\Application\Services\CurrencyServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Redis;

class DollarBlueAction extends Action
{
    private const TTL = 3600 * 24;

    private const DOLLAR_BLUE = 'dollar_blue';

    public function __construct(LoggerInterface $logger, private readonly CurrencyServiceInterface $service, private readonly Redis $redis)
    {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $cacheValue = $this->redis->get(self::DOLLAR_BLUE);

        if ($cacheValue !== false) {
           return $this->respondWithData((int)$cacheValue, 203);
        }
        $result = $this->service->getDollarBlueRate();

        $this->redis->set(self::DOLLAR_BLUE, (string)$result, self::TTL);

        return $this->respondWithData($result);
    }

}
