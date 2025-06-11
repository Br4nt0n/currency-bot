<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\CacheKeyEnum;
use App\Application\Enums\CurrencyCodeEnum;
use App\Application\Handlers\ContainerHelper;
use Redis;
use Telegram\Bot\Commands\Command;

abstract class BaseCurrencyCommand extends Command
{
    protected const int TTL = 60;

    abstract public function handle(): void;

    protected function setCurrencyCache(CurrencyCodeEnum $enum): bool
    {
        /** @var Redis $redis */
        $redis = ContainerHelper::get(Redis::class);
        $chatId = $this->getUpdate()->getChat()->get('id');

        return $redis->setex(CacheKeyEnum::CHAT_ID->format((string)$chatId), self::TTL,  $enum->value);
    }

}
