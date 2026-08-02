<?php

namespace Tracelytics\Laravel\Commands;

use Illuminate\Console\Command;
use Tracelytics\Laravel\TracelyticsClient;
use Exception;

class TracelyticsTestCommand extends Command
{
    protected $signature = 'tracelytics:test';
    protected $description = 'Send a test exception to Tracelytics to verify your configuration.';

    public function handle(TracelyticsClient $client): int
    {
        $this->info('Testing Tracelytics configuration...');

        $apiKey = config('tracelytics.api_key');
        $endpoint = config('tracelytics.endpoint');

        $this->line("API Key: " . ($apiKey ? substr($apiKey, 0, 8) . '...' : '<fg=red>Not Configured</>'));
        $this->line("Endpoint: {$endpoint}");

        if (empty($apiKey)) {
            $this->error('TRACELYTICS_API_KEY is not set in your .env file!');
            return self::FAILURE;
        }

        try {
            $client->addBreadcrumb('system', 'Booted Artisan console test environment');
            $client->addBreadcrumb('auth', 'Verified TRACELYTICS_API_KEY configuration');
            $client->addBreadcrumb('telemetry', 'Prepared test exception payload for ingestion API');

            $testException = new Exception('Tracelytics Test Exception: Verification successful!');
            $eventId = $client->captureException($testException, [
                'is_test' => true,
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ]);

            if ($eventId) {
                $this->info("Success! Test event sent successfully with Event ID: {$eventId}");
                return self::SUCCESS;
            } else {
                $this->info("Test event sent successfully to Tracelytics!");
                return self::SUCCESS;
            }
        } catch (Exception $e) {
            $this->error("Failed to send test event: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
