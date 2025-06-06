<?php

declare(strict_types=1);

namespace App\Application\Dto;

class GoogleSpotDto
{
    public function __construct(
        public string $name,
        public float $latitude,
        public float $longitude,
        public string $status,
        public ?float $rating,
        public string $vicinity,
        public ?int $user_rating,

    )
    {
    }

}
