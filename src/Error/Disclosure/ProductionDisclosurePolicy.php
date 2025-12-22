<?php

namespace JDS\Error\Disclosure;
//
// Version 1.2 Final (v1.2 §6)
//

use JDS\Error\Disclosure\DebugDisclosurePolicyInterface;

final class ProductionDisclosurePolicy implements DebugDisclosurePolicyInterface
{

    public function allowSensitiveDetails(): bool
    {
        return false;
    }
}