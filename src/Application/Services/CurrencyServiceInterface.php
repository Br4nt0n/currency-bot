<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Dto\RubDto;
use App\Application\Dto\UsdDto;

interface CurrencyServiceInterface
{
    public const string DOLLAR_BLUE = 'dollar_blue';

    public const string RUB_ARS = 'rub_ars';

    public const string RUB_USD = 'rub_usd';

    public const string USD_ARS = 'usd_ars';

    public const string USD_RUB = 'usd_rub';

    public function getDollarBlueRate(): ?float;

    public function getRubRates(): RubDto;

    public function getUsdRates(): UsdDto;
}
