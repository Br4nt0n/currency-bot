<?php

declare(strict_types=1);

namespace App\Application\Dto;

class UsdOfficialDto
{
    public function __construct(
        public ?float $buy,
        public ?float $sell
    )
    {
    }

}
