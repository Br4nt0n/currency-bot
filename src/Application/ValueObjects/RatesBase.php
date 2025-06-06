<?php

declare(strict_types=1);

namespace App\Application\ValueObjects;

abstract class RatesBase
{
    abstract function toString(): string;
}
