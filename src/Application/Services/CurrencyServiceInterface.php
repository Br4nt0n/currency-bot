<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Dto\RubDto;
use App\Application\Dto\UsdBlueDto;
use App\Application\Dto\UsdDto;

interface CurrencyServiceInterface
{
    public const string DOLLAR_BLUE_BUY = 'dollar_blue_buy';

    public const string DOLLAR_BLUE_SELL = 'dollar_blue_sell';

    public const string USD_ARS_BUY = 'usd_ars_buy';

    public const string USD_ARS_SELL = 'usd_ars_sell';

    public const string RUB_ARS = 'rub_ars';

    public const string RUB_USD = 'rub_usd';

    public const string USD_ARS = 'usd_ars';

    public const string USD_RUB = 'usd_rub';

    public function getDollarBlueRate(): UsdBlueDto;

    public function getRubRates(): RubDto;

    public function getUsdRates(): UsdDto;
}
