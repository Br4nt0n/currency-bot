<?php

declare(strict_types=1);

namespace App\Application\Enums;

enum CacheKeyEnum: string
{
    case BTC = 'btc.%s';

    case GRAPH = 'currency_%s_graph';

    case CHAT_ID = 'chat_%s';

    public function format(string ...$args): string
    {
        $lowerArgs = array_map('strtolower', $args);

        return sprintf($this->value, ...$lowerArgs);
    }
}
