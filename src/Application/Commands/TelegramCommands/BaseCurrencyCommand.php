<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\CurrencyCodeEnum;
use App\Application\Handlers\ContainerHelper;
use Redis;
use Telegram\Bot\Commands\Command;

abstract class BaseCurrencyCommand extends Command
{
    protected const string CHAT_ID = 'chat_%s';

    protected const int TTL = 60;

    abstract public function handle(): void;

    protected function setCurrencyCache(CurrencyCodeEnum $enum): bool
    {
        /** @var Redis $redis */
        $redis = ContainerHelper::get(Redis::class);
        $chatId = $this->getUpdate()->getChat()->get('id');

        return $redis->setex(sprintf(self::CHAT_ID, $chatId), self::TTL,  $enum->value);
    }

}
