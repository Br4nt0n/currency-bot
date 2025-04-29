<?php

namespace App\Application\Enums;

enum BotCommandEnum: string
{
    case START = 'start';

    case LATEST = 'latest';

    case CONVERT = 'convert';

    case CHART = 'chart';
}
