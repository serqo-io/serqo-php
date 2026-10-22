<?php
declare(strict_types=1);

namespace Serqo;

/** Dependency-light client for the public Serqo Search API. */
final class Client
{
    private string $baseUrl;

    /**
     * @param string $apiKey Secret key; keep it in server-side configuration.
     * @param string $baseUrl API origin without a trailing slash.
     * @param int $timeoutSeconds Per-request timeout, default 60 seconds.
     */
    public function __construct(
        private readonly string $apiKey,
        string $baseUrl = 'https://api.serqo.io',
        private readonly int $timeoutSeconds = 60,
    ) {
        if (trim($apiKey) === '') {
            throw new \InvalidArgumentException('apiKey is required');
        }
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /** Send one search to POST /v1/search. Searches are not retried automatically. */
    public function search(array $query, ?string $idempotencyKey = null): array
    {
        $headers = [];
        if ($idempotencyKey !== null && $idempotencyKey !== '') {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }
        return $this->request('POST', '/v1/search', $query, true, $headers);
    }

    /** Return current search engines and supported types. */
    public function engines(): array
    {
        return $this->request('GET', '/v1/engines');
    }

    /** Return the account and key identifiers associated with this key. */
    public function me(): array
    {
        return $this->request('GET', '/v1/me');
    }

    /** Check whether the product API responds; this endpoint needs no key. */
    public function health(): array
    {
        return $this->request('GET', '/v1/health', authenticated: false);
    }

    private function request(
        string $method,
        string $path,
        ?array $body = null,
        bool $authenticated = true,
        array $extraHeaders = [],
    ): array {
        $handle = curl_init($this->baseUrl . $path);
        if ($handle === false) {
            throw new \RuntimeException('Could not initialize the HTTP client');
        }
        $headers = ['Accept: application/json'];
        if ($authenticated) {
            $headers[] = 'x-api-key: ' . $this->apiKey;
        }
        foreach ($extraHeaders as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => min(10, $this->timeoutSeconds),
        ];
        if ($body !== null) {
            $encoded = json_encode($body, JSON_THROW_ON_ERROR);
            $options[CURLOPT_POSTFIELDS] = $encoded;
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
        }
        curl_setopt_array($handle, $options);
        $raw = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($handle);
        curl_close($handle);
        if ($raw === false) {
            throw new \RuntimeException('Could not reach Serqo API: ' . $curlError);
        }
        try {
            $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $error) {
            throw new \RuntimeException('Serqo API returned invalid JSON', previous: $error);
        }
        if (!is_array($payload)) {
            throw new \RuntimeException('Serqo API returned an invalid response');
        }
        if ($status < 200 || $status >= 300) {
            $apiError = is_array($payload['error'] ?? null) ? $payload['error'] : [];
            throw new SerqoError(
                $status,
                (string) ($apiError['code'] ?? 'UNKNOWN_ERROR'),
                (string) ($apiError['message'] ?? "Serqo API request failed with HTTP {$status}"),
                isset($payload['requestId']) ? (string) $payload['requestId'] : null,
                (bool) ($apiError['retryable'] ?? false),
                isset($apiError['retryAfterMs']) ? (int) $apiError['retryAfterMs'] : null,
                $apiError['details'] ?? null,
            );
        }
        if (($payload['success'] ?? false) !== true) {
            throw new \RuntimeException('Serqo API returned an unsuccessful response');
        }
        return $payload;
    }
}
