<?php

declare(strict_types=1);

namespace App\Application\Dto;

class EurDto
{
    public function __construct(
        public ?float $eurRub,
        public ?float $eurArs,
    )
    {
    }

}
