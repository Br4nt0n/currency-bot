<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\BotCommandEnum;
use App\Application\Enums\CurrencyPairEnum;
use App\Application\Handlers\ContainerHelper;
use App\Application\Services\QuickChartService;
use Redis;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;

class ChartCommand extends Command
{
    protected string $name = 'chart';
    protected string $description = '';

    public function handle()
    {
        $replyMarkup = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->inline()
            ->row([
                Keyboard::inlineButton(['text' =>'Назад', 'callback_data' => BotCommandEnum::START->value]),
            ]);

        $this->reply($replyMarkup);
    }

    private function reply($replyMarkup): void
    {
        /** @var Redis $redis */
        $redis = ContainerHelper::get(Redis::class);
        $cacheKey = sprintf(QuickChartService::CACHE_KEY, strtolower(CurrencyPairEnum::USD_RUB->value));

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
                'text' => 'Здесь пока ничего нет, но скоро появится!',
                'reply_markup' => $replyMarkup,
            ]);
        }
    }

}

