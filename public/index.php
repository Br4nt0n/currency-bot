<?php

declare(strict_types=1);

use App\Application\Clients\BlueLyticsClient;
use App\Application\Clients\ExchangeRateClient;
use App\Application\Handlers\ContainerHelper;
use App\Application\Handlers\HttpErrorHandler;
use App\Application\Handlers\ShutdownHandler;
use App\Application\ResponseEmitter\ResponseEmitter;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use GuzzleHttp\Client;
use MongoDB\Driver\ServerApi;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Telegram\Bot\Api;

ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . './..');
$dotenv->load();

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (false) { // Should be set to true in production
	$containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

// Set up repositories
$repositories = require __DIR__ . '/../app/repositories.php';
$repositories($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();

$container->set(Redis::class, function () {
    return new Redis([
       'host' => getenv('REDIS_HOST'),
       'port' => (int)getenv('REDIS_PORT'),
       'auth' => getenv('REDIS_PASSWORD'),
    ]);
});

$container->set(ExchangeRateClient::class, function () use ($container)  {
    return new ExchangeRateClient(
        $container->get(LoggerInterface::class),
        new Client(),
        getenv('EXCHANGE_URL'),
        getenv('FREE_CURRENCY_API_KEY'),
    );
});

$container->set(BlueLyticsClient::class, function () use ($container)  {
    return new BlueLyticsClient(
        $container->get(LoggerInterface::class),
        new Client(),
        getenv('DOLLAR_BLUE_URI'),
    );
});

$container->set(Api::class, function () {
    $telegram = new Api(getenv('BOT_API_KEY'));
    $telegram->setWebhook([
        'url' => getenv('APP_NAME') . 'telegram/webhook',
    ]);

    return $telegram;
});

$container->set(\MongoDB\Client::class, function () {
    $apiVersion = new ServerApi((string)ServerApi::V1);
    $user = getenv('MONGO_USERNAME');
    $pass = getenv('MONGO_PASSWORD');
    $server = getenv('MONGO_URI');
    return new \MongoDB\Client(
        uri: sprintf($server, $user, $pass),
        driverOptions: ['serverApi' => $apiVersion]
    );
});

ContainerHelper::setContainer($container);

// Register middleware
$middleware = require __DIR__ . '/../app/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

/** @var SettingsInterface $settings */
$settings = $container->get(SettingsInterface::class);

$displayErrorDetails = $settings->get('displayErrorDetails');
$logError = $settings->get('logError');
$logErrorDetails = $settings->get('logErrorDetails');

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Error Handler
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Body Parsing Middleware
$app->addBodyParsingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logError, $logErrorDetails);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);

