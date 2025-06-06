<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\BotCommandEnum;
use App\Application\Exceptions\CurrencyException;
use App\Application\Handlers\ContainerHelper;
use App\Infrastructure\Services\CurrencyService;
use App\Application\Services\CurrencyServiceInterface;
use Redis;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class LatestRatesCommand extends Command
{
    protected string $name = BotCommandEnum::LATEST->value;

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
            'parse_mode' => 'HTML',
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
        $rubArs = $redis->get(CurrencyServiceInterface::RUB_ARS);

        if ($usdRub === false || $usdArs === false || $rubArs === false) {
            throw new CurrencyException('Latest rates currency are not set!');
        }

        $usdBlueSell = round((float)$usdBlueSell, 2);
        $usdBlueBuy = round((float)$usdBlueBuy, 2);
        $usdRub = round((float)$usdRub, 2);
        $usdArs = round((float)$usdArs, 2);
        $rubArs = round((float)$rubArs, 2);

        return "На 🗓️ $date
             <b>$ Доллар составляет:</b>
                Блю курс:
                    Продажа: $usdBlueSell
                    Покупка: $usdBlueBuy
                Официальный (рубли): 
                    Покупка: $usdRub
                Официальный (песо): 
                    Покупка: $usdArs
                    
             <b>₽ Рубль составляет:</b> 
                Официальный (песо): 
                    Покупка: $rubArs
        ";
    }
}


