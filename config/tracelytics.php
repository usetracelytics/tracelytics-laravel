<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tracelytics Project API Key
    |--------------------------------------------------------------------------
    |
    | The API Key identifies your project and authorizes event ingestion.
    | Obtain your API Key from Tracelytics Settings -> Projects.
    |
    */

    'api_key' => env('TRACELYTICS_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Ingestion API Endpoint
    |--------------------------------------------------------------------------
    |
    | Base endpoint where Tracelytics Ingestion Service listens for events.
    |
    */

    'endpoint' => env('TRACELYTICS_ENDPOINT', 'http://localhost:8080/v1/events'),

    /*
    |--------------------------------------------------------------------------
    | Environment & Release
    |--------------------------------------------------------------------------
    |
    | Specify application environment and release version for error tracking.
    |
    */

    'environment' => env('TRACELYTICS_ENV', env('APP_ENV', 'production')),

    'release' => env('TRACELYTICS_RELEASE', env('APP_VERSION', '1.0.0')),

    /*
    |--------------------------------------------------------------------------
    | Capture Uncaught Exceptions
    |--------------------------------------------------------------------------
    |
    | Automatically report uncaught exceptions to Tracelytics.
    |
    */

    'enabled' => env('TRACELYTICS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Timeout (seconds)
    |--------------------------------------------------------------------------
    |
    */

    'timeout' => 2.0,

];
