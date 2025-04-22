<?php

declare(strict_types=1);

namespace App\Application\Dto;

class UsdDto
{
    public function __construct(
        public ?float $usdRub,
        public ?float $usdArs,
    )
    {
    }

}
