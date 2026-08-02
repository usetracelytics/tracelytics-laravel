<?php

namespace Tracelytics\Laravel;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Throwable;

class TracelyticsClient
{
    protected string $apiKey;
    protected string $endpoint;
    protected string $environment;
    protected string $release;
    protected bool $enabled;
    protected float $timeout;
    protected Client $httpClient;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->endpoint = $config['endpoint'] ?? 'http://localhost:8080/v1/events';
        $this->environment = $config['environment'] ?? 'production';
        $this->release = $config['release'] ?? '1.0.0';
        $this->enabled = $config['enabled'] ?? true;
        $this->timeout = $config['timeout'] ?? 2.0;

        $this->httpClient = new Client([
            'timeout' => $this->timeout,
            'http_errors' => false,
        ]);
    }

    public function captureException(Throwable $exception, array $extraContext = []): ?string
    {
        if (!$this->enabled || empty($this->apiKey)) {
            return null;
        }

        try {
            $payload = $this->buildPayload($exception, $extraContext);

            $response = $this->httpClient->post($this->endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-Tracelytics-SDK' => 'tracelytics-laravel/1.0.0',
                ],
                'json' => $payload,
            ]);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $data = json_decode((string) $response->getBody(), true);
                return $data['event_id'] ?? null;
            }
        } catch (Throwable $e) {
            // Silently fail to avoid crashing the user's application
            error_log('[Tracelytics SDK Exception Report Failed] ' . $e->getMessage());
        }

        return null;
    }

    protected function buildPayload(Throwable $exception, array $extraContext): array
    {
        $culprit = sprintf('%s:%d', basename($exception->getFile()), $exception->getLine());
        $title = sprintf('%s: %s', get_class($exception), $exception->getMessage());

        // Parse Stack Trace Frames
        $frames = [];
        foreach ($exception->getTrace() as $trace) {
            $frames[] = [
                'file' => isset($trace['file']) ? basename($trace['file']) : '[internal]',
                'abs_path' => $trace['file'] ?? '',
                'line' => $trace['line'] ?? 0,
                'function' => $trace['function'] ?? '',
                'class' => $trace['class'] ?? '',
                'type' => $trace['type'] ?? '',
            ];
        }

        // Add root origin frame
        array_unshift($frames, [
            'file' => basename($exception->getFile()),
            'abs_path' => $exception->getFile(),
            'line' => $exception->getLine(),
            'function' => '{main}',
            'class' => get_class($exception),
            'type' => '::',
        ]);

        // User Context
        $userContext = [];
        if (Auth::check()) {
            $user = Auth::user();
            $userContext = [
                'id' => (string) ($user->id ?? ''),
                'email' => $user->email ?? '',
                'name' => $user->name ?? '',
            ];
        }

        // Request Context
        $requestContext = [
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'headers' => array_filter(Request::header(), function ($key) {
                return !in_array(strtolower($key), ['authorization', 'cookie']);
            }, ARRAY_FILTER_USE_KEY),
        ];

        return [
            'title' => $title,
            'culprit' => $culprit,
            'severity' => 'critical',
            'type' => 'error',
            'environment' => $this->environment,
            'release' => $this->release,
            'timestamp' => now()->toIso8601String(),
            'stacktrace' => [
                'frames' => $frames,
            ],
            'user_context' => $userContext,
            'request_context' => $requestContext,
            'tags' => array_merge([
                'platform' => 'laravel',
                'php_version' => PHP_VERSION,
            ], $extraContext),
        ];
    }
}
