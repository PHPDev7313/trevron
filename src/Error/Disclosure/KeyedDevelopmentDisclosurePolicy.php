<?php

namespace JDS\Error\Disclosure;
//
// Version 1.2 Final (v1.2 §6)
//

use JDS\Error\Disclosure\DebugDisclosurePolicyInterface;

final class KeyedDevelopmentDisclosurePolicy implements DebugDisclosurePolicyInterface
{
    public function __construct(
        private readonly string $expectedKey,
        private readonly string $serverVarName = 'JDS_DEBUG_KEY'
    ) {}

    public function allowSensitiveDetails(): bool
    {
        $provided = $_SERVER[$this->serverVarName]?? '';
        if ($provided === '' || $this->expectedKey === '') {
            return false;
        }

        return hash_equals($this->expectedKey, $provided);
    }
}

