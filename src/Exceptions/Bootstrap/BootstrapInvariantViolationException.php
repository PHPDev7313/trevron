<?php

declare(strict_types=1);

namespace JDS\Exceptions\Bootstrap;

use JDS\Exceptions\Bootstrap\BootstrapExecption;

final class BootstrapInvariantViolationException extends BootstrapExecption
{
    public function __construct(string $message = "")
    {
        parent::__construct(
            "[BOOTSTRAP INVARIANT VIOLATION] {$message}"
        );
    }
}

