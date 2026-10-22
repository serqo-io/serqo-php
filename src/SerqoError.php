<?php
declare(strict_types=1);

namespace Serqo;

/** API error carrying the HTTP status and structured Serqo error fields. */
final class SerqoError extends \RuntimeException
{
    public function __construct(
        public readonly int $status,
        public readonly string $apiCode,
        string $message,
        public readonly ?string $requestId = null,
        public readonly bool $retryable = false,
        public readonly ?int $retryAfterMs = null,
        public readonly mixed $details = null,
    ) {
        parent::__construct($message);
    }
}
