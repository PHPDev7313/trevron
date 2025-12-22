<?php

namespace JDS\Error;
//
// Version 1.2 Final (v1.2 §5)
//

use Throwable;

final class ErrorContext
{
    /**
     * @param array<string, mixed> $debug
     */
    public function __construct(
        public readonly int $httpStatus,
        public readonly StatusCode $statusCode,
        public readonly StatusCategory $category,
        public readonly string $publicMessage,
        public readonly ?Throwable $exception = null,
        public readonly array $debug = []
    ) {}

    public function hasDebug(): bool
    {
        return $this->debug !== [];
    }
}

