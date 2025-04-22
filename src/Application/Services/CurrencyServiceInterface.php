<?php

declare(strict_types=1);

namespace App\Application\Services;

interface CurrencyServiceInterface
{
    public const string DOLLAR_BLUE = 'dollar_blue';

    public const string RUB_ARS = 'rub_ars';

    public const string RUB_USD = 'rub_usd';

    public function getDollarBlueRate(): ?float;

    public function getUsdRubRate(): ?float;

    public function getUsdArsRate(): ?float;

    public function getRubArsRate(): ?float;

    public function getRubUsdRate(): ?float;
}
