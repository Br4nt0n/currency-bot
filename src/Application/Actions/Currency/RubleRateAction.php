<?php

declare(strict_types=1);

namespace App\Application\Actions\Currency;

use App\Application\Actions\Action;
use App\Application\Services\CurrencyService;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Redis;

class RubleRateAction extends Action
{
    private const TTL = 3600 * 24;

    private const RUB_ARS = 'rub_ars';

    private const RUB_USD = 'rub_usd';

    public function __construct(LoggerInterface $logger, private readonly CurrencyService $service, private readonly Redis $redis)
    {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $arsCacheValue = $this->redis->get(self::RUB_ARS);
        $usdCacheValue = $this->redis->get(self::RUB_USD);

        if ($arsCacheValue !== false && $usdCacheValue !== false) {
           return $this->respondWithData(['ARS' => $arsCacheValue, 'USD' => $usdCacheValue], 203);
        }

        $arsRate = $this->service->getRubArsRate();
        $usdRate = $this->service->getRubUsdRate();

        if (is_null($arsRate) || is_null($usdRate)) {
            return $this->respondWithData('Rates not found!', StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $this->redis->set(self::RUB_ARS, (string)$arsRate, self::TTL);
        $this->redis->set(self::RUB_USD, (string)$usdRate, self::TTL);

        return $this->respondWithData(['ARS' => $arsRate, 'USD' => $usdRate]);
    }

}
