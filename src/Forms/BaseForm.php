<?php

declare(strict_types=1);

namespace JDS\Forms;

use JDS\Contracts\Validation\ValidatorInterface;
use JDS\Validation\ValidationResult;

abstract class BaseForm
{
    protected array $data = [];
    protected array $errors = [];
    protected ?ValidationResult $result = null;

    public function __construct(
        protected ValidatorInterface $validator,
    )
    {
    }

    abstract public function rules(): array;

    public function setData(array $input): void
    {
        $this->data = $input;
    }

    public function validate(): bool
    {
        $this->result = $this->validator->validate($this->data, $this->rules());
        $this->errors = $this->result->errors();
        return $this->result->passes();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getData(): array
    {
        return $this->data;
    }
}

