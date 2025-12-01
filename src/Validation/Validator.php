<?php

declare(strict_types=1);

namespace JDS\Validation;

use JDS\Contracts\Validation\ValidatorInterface;

class Validator implements ValidatorInterface
{
    public function __construct(
        private RuleRegistery $registery
    )
    {
    }

    public function validate(array $data, array $rules): ValidationResult
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $ruleDef) {
                [$ruleName, $parms] = $this->parseRule($ruleDef);

                $rule = $this->registery->get($ruleName);

                $error = $this->validate($field, $value, $params);

                if ($error !== null) {
                    $errors[$field] = $error;
                }
            }
        }
        return new ValidationResult($errors);
    }

    /**
     * Parse 'min:3' => ['min', ['3']]
     */
    private function parseRule(string $ruleDef): array
    {
        if (str_contains($ruleDef, ':')) {
            [$name, $paramStr] = explode(':', $ruleDed, 2);
            $params = explode(',', $paramStr);
            return [$name, $params];
        }

        return [$ruleDef, []];
    }
}

