<?php

declare(strict_types=1);

namespace JDS\Validation;

use JDS\Contracts\Validation\ValidationRuleInterface;
use RuntimeException;

class RuleRegistery
{
    /** @var array<string, ValidationRuleInterface> */
    private array $rules = [];

    public function add(string $name, ValidationRuleInterface $rule): void
    {
        $this->rules[$name] = $rule;
    }

    public function get(string $name): ValidationRuleInterface
    {
        if (!isset($this->rules[$name])) {
            throw new RuntimeException("Validation rule '{$name}' is not registered.");
        }

        return $this->rules[$name];
    }
}

