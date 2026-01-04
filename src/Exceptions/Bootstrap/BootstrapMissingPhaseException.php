<?php

declare(strict_types=1);

namespace JDS\Exceptions\Bootstrap;

use JDS\Exceptions\Bootstrap\BootstrapException;

final class BootstrapMissingPhaseException extends BootstrapException
{
    public function __construct(string $message)
    {
        parent::__construct("[BOOTSTRAP MISSING PHASE] {$message}");
    }
}

