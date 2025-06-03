<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\BotCommandEnum;
use App\Application\Handlers\ContainerHelper;
use App\Application\Services\QuickChartService;
use Redis;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;

class CurrencyChartCommand extends Command
{
    protected string $name = BotCommandEnum::CURRENCY_CHART->value;

    public function handle(): void
    {
        $callback = $this->getUpdate()->callbackQuery?->get('data');

        $replyMarkup = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->inline()
            ->row([
                Keyboard::inlineButton(['text' =>'В начало', 'callback_data' => BotCommandEnum::START->value]),
            ]);

        $this->reply($callback, $replyMarkup);
    }

    private function reply(string $callback, $replyMarkup): void
    {
        /** @var Redis $redis */
        $redis = ContainerHelper::get(Redis::class);
        $cacheKey = sprintf(QuickChartService::CACHE_KEY, strtolower($callback));

        if ($redis->exists($cacheKey)) {
            $content = $redis->get($cacheKey);

            if ($content !== false) {
                $this->replyWithPhoto([
                    'photo' => InputFile::createFromContents(base64_decode($content), 'chart.png'),
                    'reply_markup' => $replyMarkup,
                ]);
            }
        } else {
            $this->replyWithMessage([
                'text' => "Этого $callback графика пока нет, но скоро появится!",
                'reply_markup' => $replyMarkup,
            ]);
        }
    }

}
