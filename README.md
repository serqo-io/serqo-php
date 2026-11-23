# Serqo PHP SDK

Official PHP client for the [Serqo Search API](https://serqo.io). Retrieve clean, structured Google & Bing SERP results in JSON with zero proxy setup. Get started with 500 free requests on [serqo.io](https://serqo.io).

Requires PHP 8.1 or later and the cURL extension.

## Install

```sh
composer require serqo-io/serqo-php
```

## Quick start

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Serqo\Client;

$client = new Client(getenv('SERQO_API_KEY'));

// Web Search
$response = $client->search([
    'q' => 'espresso machines',
    'engine' => 'google',
    'type' => 'web',
    'gl' => 'us',
    'hl' => 'en',
]);

foreach ($response['data']['results'] as $result) {
    echo $result['title'] ?? '', PHP_EOL;
}

// Autocomplete / Search Suggestions
$suggestions = $client->autocomplete([
    'q' => 'artificial intell',
    'engine' => 'google',
    'gl' => 'us',
    'hl' => 'en',
]);

foreach ($suggestions['data']['suggestions'] as $item) {
    echo $item['text'] ?? '', PHP_EOL;
}
```

Keep the API key in server-side configuration. Methods return the full response envelope as associative arrays.

## Methods and errors

- `search(array $query, ?string $idempotencyKey = null)` sends `POST /v1/search`.
- `autocomplete(array $query, ?string $idempotencyKey = null)` sends `POST /v1/autocomplete` for real-time query suggestions or expansion.
- `engines()`, `me()`, and `health()` call the corresponding endpoints.
- API failures throw `Serqo\SerqoError` with `status`, `apiCode`, `requestId`, `retryable`, `retryAfterMs`, and `details` properties.
- Searches are not retried automatically. Reuse the same idempotency key only for identical parameters.
- See the [Serqo API Documentation](https://serqo.io/docs) and [shared API reference](API.md) for all search parameters, response fields, and pagination behavior.

---

## Documentation & Support

- **Official Website**: [https://serqo.io](https://serqo.io)
- **Web Documentation**: [https://serqo.io/docs](https://serqo.io/docs)
- **API Reference**: [API.md](API.md)
- **Get API Key (500 Free Credits)**: [https://serqo.io](https://serqo.io)
- **License**: MIT
