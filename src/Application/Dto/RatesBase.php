<?php

declare(strict_types=1);

namespace App\Application\Dto;

abstract class RatesBase
{
    abstract function toString(): string;
}
