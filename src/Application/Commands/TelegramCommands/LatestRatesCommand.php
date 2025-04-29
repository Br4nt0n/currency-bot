<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\BotCommandEnum;
use App\Application\Handlers\ContainerHelper;
use App\Application\Services\CurrencyService;
use App\Application\Services\CurrencyServiceInterface;
use Redis;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class LatestRatesCommand extends Command
{
    protected string $name = BotCommandEnum::LATEST->value;
    protected string $description = '';

    public function handle(): void
    {

        $replyMarkup = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->inline()
            ->row([
                Keyboard::inlineButton(['text' =>'Назад', 'callback_data' => BotCommandEnum::START]),
            ]);

        $text = $this->formRates();

        $this->replyWithMessage([
            'text' => $text,
            'reply_markup' => $replyMarkup,
        ]);
    }

    private function formRates(): string
    {
        /** @var CurrencyServiceInterface $service */
        $service = ContainerHelper::get(CurrencyService::class);
        /** @var Redis $redis */
        $redis = ContainerHelper::get(Redis::class);
        $date = date('d.m.Y');

        $usdBlueBuy = $redis->get(CurrencyServiceInterface::DOLLAR_BLUE_BUY);
        $usdBlueSell = $redis->get(CurrencyServiceInterface::DOLLAR_BLUE_SELL);

        if ($usdBlueBuy === false || $usdBlueSell === false) {
            $usdDto = $service->getDollarBlueRate();
            $usdBlueSell = $usdDto->sell;
            $usdBlueBuy = $usdDto->buy;
        }

        $usdRub = $redis->get(CurrencyServiceInterface::USD_RUB);
        $usdArs = $redis->get(CurrencyServiceInterface::USD_ARS);

        if ($usdRub === false || $usdArs === false) {
            $usdRate = $service->getUsdRates();
            $usdRub = $usdRate->usdRub;
            $usdArs = $usdRate->usdArs;
        }

        return "На $date доллар составляет: 
                Блю курс:
                    Покупка: $usdBlueSell
                    Продажа: $usdBlueBuy
                Официальный (рубли): 
                    Покупка: $usdRub
                Официальный (песо): 
                    Продажа: $usdArs
        ";
    }
}


