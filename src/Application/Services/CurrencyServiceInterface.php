<?php

declare(strict_types=1);

namespace App\Application\Services;

interface CurrencyServiceInterface
{
    public function getDollarBlueRate(): int;

    public function getUsdRubRate(): ?float;

    public function getUsdArsRate(): ?float;

    public function getRubArsRate(): ?float;

    public function getRubUsdRate(): ?float;
}
