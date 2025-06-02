<?php

declare(strict_types=1);

use App\Application\Actions\Conversion\ConversionAction;
use App\Application\Actions\Currency\DollarBlueAction;
use App\Application\Actions\Currency\DollarRateAction;
use App\Application\Actions\Currency\RubleRateAction;
use App\Application\Actions\Telegram\BotWebhook;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $body = '<button><a href="/get-dollar-blue">Get Dollar Blue</a></button>
                <button><a href="/get-dollar-rates">Get Dollar</a></button>
                <button><a href="/get-ruble-rates">Get Rubble Rate</a></button>
                <button><a href="/conversion">Conversion</a></button>';
        $response->getBody()->write($body);
        return $response;
    });

    $app->get('/get-dollar-blue', DollarBlueAction::class);
    $app->get('/get-dollar-rates', DollarRateAction::class);
    $app->get('/get-ruble-rates', RubleRateAction::class);

    $app->get('/conversion', ConversionAction::class);

    $app->map(['GET', 'POST'], '/telegram/webhook', BotWebhook::class);
};
