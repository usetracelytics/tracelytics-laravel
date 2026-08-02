<?php

namespace Tracelytics\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null captureException(\Throwable $exception, array $extraContext = [])
 *
 * @see \Tracelytics\Laravel\TracelyticsClient
 */
class Tracelytics extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tracelytics';
    }
}
