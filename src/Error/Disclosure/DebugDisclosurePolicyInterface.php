<?php

namespace JDS\Error\Disclosure;
//
// Version 1.2 Final (v1.2 §6)
//

interface DebugDisclosurePolicyInterface
{
    public function allowSensitiveDetails(): bool;
}

