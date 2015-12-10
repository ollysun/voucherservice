<?php


require_once __DIR__.'/../vendor/autoload.php';
use Monolog\Logger;
use Iroko\Logging\Monolog\Handlers\SQSHandler;
use Monolog\Formatter\JsonFormatter;

use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

Dotenv::load(__DIR__.'/../');

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

$app->withFacades();

$app->withEloquent();


/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    Voucher\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    Voucher\Console\Kernel::class
);

// Import logging config
$app->configure('iroko_logger');

$app->singleton(
    'log',
    function ($app) {
        $log_handlers = array();
        $log_processors = array();

        // Do we want to push our logs to an SQS queue?
        if ($app['config']['iroko_logger.enabled']) {
            $config = $app['config']['iroko_logger.aws'];
            // Setup SQS Client
            $sqs_client = new \Aws\Sqs\SqsClient($config);
            // Setup our SQS handler
            $sqsHandler = new SQSHandler($sqs_client, $app['config']['iroko_logger.endpoint_url']);
            $sqsHandler->setFormatter(new JsonFormatter());
            $log_handlers[] = $sqsHandler;

            $ec2processor = new \Iroko\Logging\Monolog\Processor\EC2MetadataProcessor();
            $log_processors[] = $ec2processor;
        }

        // If we also want to enable the default handler configured in app config
        if ($app['config']['iroko_logger.allow_default_logging']) {
            $streamHandler = new StreamHandler(storage_path('logs/lumen.log'), Logger::DEBUG);
            $streamHandler->setFormatter(new LineFormatter(null, null, true, true));
            $log_handlers[] = $streamHandler;
        }

        $environment = getenv('ENVIRONMENT').'.'.getenv('SERVICE');
        return new Logger($environment, $log_handlers, $log_processors);
    }
);

$app->register(Iroko\Analytics\Providers\AnalyticsServiceProvider::class);

$app->register(Iroko\Notify\Providers\NotifyServiceProvider::class);

if(!class_exists('Notification'))
    class_alias('Iroko\Notify\Facades\NotifyFacade', 'Notification');

//class_alias('Iroko\Analytics\Facades\AnalyticsFacade', 'Analytics');
$app->configure('iroko_analytics');

$app->configure('sqs');
$app->configure('s3');

$app->configure('iroko_notify');
/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|

*/

$app->middleware([
    'authApi' => Iroko\AuthApi\Middleware\AuthApiMiddleware::class,
]);
/*
$app->middleware([
    Subscription\Http\Middleware\LumenCors::class
]);*/
//     // Illuminate\Cookie\Middleware\EncryptCookies::class,
//     // Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
//     // Illuminate\Session\Middleware\StartSession::class,
//     // Illuminate\View\Middleware\ShareErrorsFromSession::class,
//     // Laravel\Lumen\Http\Middleware\VerifyCsrfToken::class,
// ]);

// $app->routeMiddleware([

// ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

// $app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);
$app->register(Iroko\AuthApi\Providers\AuthApiServiceProvider::class);

//$app->register(GatherContent\LaravelFractal\LaravelFractalServiceProvider::class);
//class_alias(GatherContent\LaravelFractal\LaravelFractalFacade::class, 'Fractal');
/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->group(['namespace' => 'Voucher\Http\Controllers'], function ($app) {
    require __DIR__.'/../app/Http/routes.php';
});

return $app;
