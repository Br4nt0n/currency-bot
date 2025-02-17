<?php

declare(strict_types=1);

namespace App\Application\Services;

interface CurrencyServiceInterface
{
    public function getDollarBlueRate(): int;

    public function getRubbleRate(): int;

    public function getPesoRate(): int;
}
