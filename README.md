# Serqo PHP SDK

Official PHP client for the Serqo Search API. Requires PHP 8.1 or later and the cURL extension.

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
```

Keep the API key in server-side configuration. Methods return the full response envelope as associative arrays.

## Methods and errors

- `search($query, $idempotencyKey = null)` sends `POST /v1/search`.
- `engines()`, `me()`, and `health()` call the corresponding endpoints.
- API failures throw `Serqo\SerqoError` with `status`, `apiCode`, `requestId`, `retryable`, `retryAfterMs`, and `details` properties.
- Searches are not retried automatically. Reuse the same idempotency key only for identical parameters.
- See the [shared API reference](API.md) for all search parameters, response fields, and pagination behavior.
