<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 19, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: RoutingFINALv12ARCHITECTURE.md
 */

namespace JDS\Exceptions\Error;

use JDS\Error\StatusCategory;
use JDS\Error\StatusCode;
use RuntimeException;
use Throwable;

class StatusException extends RuntimeException
{
    public function __construct(
        private readonly StatusCode $statusCode,
        ?string                     $message = null,
        ?Throwable                  $previous = null
    )
    {
        $finalMessage = $message ?? $this->statusCode->defaultMessage();

        parent::__construct(
            $finalMessage,
            $statusCode->value,
            $previous
        );
    }

    public function getStatusCodeEnum(): StatusCode
    {
        return $this->statusCode;
    }

    public function getStatusCategory(): StatusCategory
    {
        return $this->statusCode->category();
    }

    public function getStatusCode(): StatusCode
    {
        return $this->statusCode;
    }

    public function getHttpStatus(): int
    {
        return $this->statusCode->valueInt();
    }
}

