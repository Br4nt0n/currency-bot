<?php

namespace App\Application\Enums;

enum CacheKeyEnum: string
{
    case BTC = 'btc.%s';

    case GRAPH = 'currency_%s_graph';

    public function format(string ...$args): string
    {
        $lowerArgs = array_map('strtolower', $args);

        return sprintf($this->value, ...$lowerArgs);
    }
}
