<?php

namespace JDS\Error\Sanitization;
//
// Version 1.2 Final (v1.2 §7)
//

use JDS\Contracts\Error\Disclosure\DebugDisclosurePolicyInterface;
use JDS\Error\ErrorContext;

final class ErrorSanitizer
{
    public function __construct(
        private readonly DebugDisclosurePolicyInterface $policy
    ) {}

    public function sanitize(ErrorContext $context): ErrorContext
    {
        if ($this->policy->allowSensitiveDetails()) {
            return $context;
        }

        //
        // fail-closed: strip exeception + debug
        //
        return new ErrorContext(
            httpStatus: $context->httpStatus,
            statusCode: $context->statusCode,
            category: $context->category,
            publicMessage: $context->publicMessage,
            exception: null,
            debug: []
        );
    }
}

