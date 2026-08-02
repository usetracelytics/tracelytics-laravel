<?php

namespace Tracelytics\Laravel;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Throwable;

class TracelyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tracelytics.php', 'tracelytics');

        $this->app->singleton(TracelyticsClient::class, function ($app) {
            return new TracelyticsClient($app['config']->get('tracelytics', []));
        });

        $this->app->alias(TracelyticsClient::class, 'tracelytics');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/tracelytics.php' => config_path('tracelytics.php'),
            ], 'tracelytics-config');

            $this->commands([
                Commands\TracelyticsTestCommand::class,
            ]);
        }

        // Register Exception Report Listener
        $this->registerExceptionHandler();
    }

    protected function registerExceptionHandler(): void
    {
        try {
            $handler = $this->app->make(ExceptionHandler::class);

            if (method_exists($handler, 'reportable')) {
                $handler->reportable(function (Throwable $e) {
                    /** @var TracelyticsClient $client */
                    $client = $this->app->make(TracelyticsClient::class);
                    $client->captureException($e);
                });
            }
        } catch (Throwable $e) {
            // Silently swallow binding errors during early bootstrap
        }
    }
}
