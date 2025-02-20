<?php

declare(strict_types=1);

namespace App\Application\Actions\Currency;

use App\Application\Actions\Action;
use App\Application\Services\CurrencyServiceInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Redis;

class DollarRateAction extends Action
{
    private const TTL = 3600 * 24;

    private const USD_ARS = 'usd_ars';

    private const USD_RUB = 'usd_rub';

    public function __construct(
        LoggerInterface $logger,
        private readonly Redis $redis,
        private readonly CurrencyServiceInterface $service
    )
    {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $arsCacheValue = $this->redis->get(self::USD_ARS);
        $rubCacheValue = $this->redis->get(self::USD_RUB);

        if ($arsCacheValue !== false && $rubCacheValue !== false) {
            return $this->respondWithData(['ARS' => $arsCacheValue, 'RUB' => $rubCacheValue], StatusCodeInterface::STATUS_ACCEPTED);
        }

        $arsRate = $this->service->getUsdArsRate();
        $rubRate = $this->service->getUsdRubRate();

        if (is_null($arsRate) || is_null($rubRate)) {
            return $this->respondWithData('Rates not found!', StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $this->redis->set(self::USD_ARS, (string)$arsRate, self::TTL);
        $this->redis->set(self::USD_RUB, (string)$rubRate, self::TTL);

        return $this->respondWithData(['ARS' => $arsRate, 'RUB' => $rubRate]);
    }

}
