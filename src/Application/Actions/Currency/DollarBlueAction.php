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
    public function __construct(
        LoggerInterface $logger,
        private readonly CurrencyServiceInterface $service,
        private readonly Redis $redis
    )
    {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $cacheBuyValue = $this->redis->get(CurrencyServiceInterface::DOLLAR_BLUE_BUY);
        $cacheSellValue = $this->redis->get(CurrencyServiceInterface::DOLLAR_BLUE_SELL);

        if ($cacheBuyValue !== false && $cacheSellValue !== false) {
           return $this->respondWithData(['buy' => $cacheBuyValue, 'sell' => $cacheSellValue], 203);
        }

        $blueDto = $this->service->getDollarBlueRate();

        return $this->respondWithData(['buy' => $blueDto->buy, 'sell' => $blueDto->sell]);
    }

}
