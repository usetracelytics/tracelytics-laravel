<?php

namespace Tracelytics\Laravel;

use Illuminate\Foundation\Configuration\Exceptions;
use Throwable;

class Integration
{
    public static function handles(Exceptions $exceptions): void
    {
        $exceptions->reportable(function (Throwable $e) {
            if (app()->bound(TracelyticsClient::class)) {
                app(TracelyticsClient::class)->captureException($e);
            }
        });
    }
}
