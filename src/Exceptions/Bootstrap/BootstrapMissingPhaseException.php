<?php

declare(strict_types=1);

namespace JDS\Exceptions\Bootstrap;

use JDS\Exceptions\Bootstrap\BootstrapExecption;

final class BootstrapMissingPhaseException extends BootstrapExecption
{
    public function __construct(string $message)
    {
        parent::__construct("[BOOTSTRAP MISSING PHASE] {$message}");
    }
}

