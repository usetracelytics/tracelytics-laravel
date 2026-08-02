# Tracelytics SDK for Laravel

[![Latest Stable Version](https://img.shields.io/packagist/v/tracelytics/tracelytics-laravel.svg?style=flat-square)](https://packagist.org/packages/tracelytics/tracelytics-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/tracelytics/tracelytics-laravel.svg?style=flat-square)](https://packagist.org/packages/tracelytics/tracelytics-laravel)
[![License](https://img.shields.io/packagist/l/tracelytics/tracelytics-laravel.svg?style=flat-square)](https://github.com/tracelytics/tracelytics-laravel/blob/main/LICENSE)

Official Laravel integration package for **Tracelytics** — AI-powered observability, exception tracking, and automated Root Cause Analysis (RCA).

---

## ⚡ Features

- 🚀 **Zero Overhead Exception Capturing**: Asynchronous HTTP delivery with non-blocking fail-safe mechanisms.
- 🎯 **Laravel 11+ & 10/9 Support**: First-class support for new `bootstrap/app.php` handlers as well as legacy `app/Exceptions/Handler.php`.
- 🔍 **Rich Context Extraction**: Captures stack trace, request parameters, headers, active environment, and user sessions safely.
- 🛠️ **Artisan Verification Command**: Built-in `php artisan tracelytics:test` command to instantly verify your connection.
- 🤖 **AI RCA Prepared Payload**: Structures stack traces for instant AI-driven automated code fixes.

---

## 📦 Installation

Install the package via Composer:

```bash
composer require tracelytics/tracelytics-laravel
```

---

## ⚙️ Configuration

Add your project's **Ingestion API Key** (and optional custom endpoint) to your `.env` file:

```env
# Required: Your Tracelytics Project API Key (Settings -> Projects)
TRACELYTICS_API_KEY=pk_live_your_project_key_here

# Required: Tracelytics Ingestion Service API Endpoint
TRACELYTICS_ENDPOINT=http://localhost:8080/v1/events
```

### Publishing Configuration (Optional)

To customize timeouts, environment defaults, or fallback options, publish the config file:

```bash
php artisan vendor:publish --tag=tracelytics-config
```

This creates `config/tracelytics.php`.

---

## 🚀 Setup & Registration

### For Laravel 11+

Register Tracelytics inside your application's `bootstrap/app.php` file:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Tracelytics\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ...
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
    })->create();
```

---

### For Laravel 10 / 9

Register Tracelytics inside `app/Exceptions/Handler.php`:

```php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Tracelytics\Laravel\TracelyticsClient;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound(TracelyticsClient::class)) {
                app(TracelyticsClient::class)->captureException($e);
            }
        });
    }
}
```

---

## 🧪 Testing Your Configuration

Verify your API key and connection to the Tracelytics Ingestion server by running the built-in Artisan command:

```bash
php artisan tracelytics:test
```

If successful, you will see:
```text
Testing Tracelytics configuration...
API Key: pk_live_...
Endpoint: http://localhost:8080/v1/events
Success! Test event sent successfully to Tracelytics!
```

---

## 🛠️ Manual Exception Reporting

You can also manually report caught exceptions using the Facade or Dependency Injection:

```php
use Tracelytics\Laravel\Facades\Tracelytics;

try {
    // Dangerous operation
} catch (\Throwable $e) {
    Tracelytics::captureException($e, [
        'extra_info' => 'Payment processing context',
    ]);
}
```

---

## 📄 License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
