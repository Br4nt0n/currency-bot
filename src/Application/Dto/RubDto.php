<?php

declare(strict_types=1);

namespace App\Application\Dto;

class RubDto
{
    public function __construct(
        public ?float $rubUsd,
        public ?float $rubArs,
    )
    {
    }

}
