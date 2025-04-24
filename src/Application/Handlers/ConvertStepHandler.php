<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

class ConvertStepHandler
{
    public static function handle(Api $telegram, Update $update)
    {
        $message = $update->getMessage();
        $chatId = $update->getChat()->get('id');
        $text = $message->get('text');

        $telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "$chatId, $text",
        ]);
    }
}
