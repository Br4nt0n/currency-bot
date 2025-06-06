<?php

declare(strict_types=1);

namespace App\Application\Commands\TelegramCommands;

use App\Application\Enums\BotCommandEnum;
use App\Application\Handlers\ContainerHelper;
use App\Infrastructure\Services\GoogleMapService;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Objects\Location;

class ExchangeSpotCommand extends Command
{
    protected string $description = BotCommandEnum::EXCHANGE->value;

    public function handle(): void
    {
        /** @var Location $location */
        $location = $this->update->getMessage()->get('location');

        /** @var  GoogleMapService $service */
        $service = ContainerHelper::get(GoogleMapService::class);
        $result = $service->findExchangeSpots($location->latitude, $location->longitude);

        if ($result->count() === 0) {
            $this->replyWithMessage([
                'parse_mode' => 'HTML',
                'text' => 'Рядом с вами не удалось ничего найти',
            ]);
        }

        $text = '';
        foreach ($result as $spot) {
            $lat = $spot->latitude;
            $lng = $spot->longitude;
            $mapLink = "https://www.google.com/maps/search/?api=1&query=$lat,$lng";

            $text .= "📍 <b>$spot->name</b>\n";
            $text .= "📫 $spot->vicinity\n";
            $text .= $spot->rating ? "⭐️ $spot->rating\n" : "";
            $text .= "🗺️ <a href=\"$mapLink\">Открыть в Google Maps</a>\n\n";
        }

        $this->replyWithMessage([
            'parse_mode' => 'HTML',
            'text' => $text,
        ]);
    }

}
