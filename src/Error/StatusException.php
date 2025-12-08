<?php

namespace JDS\Error;

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
}

