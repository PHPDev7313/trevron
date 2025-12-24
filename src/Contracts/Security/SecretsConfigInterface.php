<?php

namespace JDS\Contracts\Security;

interface SecretsConfigInterface
{
    public function secretsFile(): string;
    public function schemaFile(): string;
    public function appKeyBase64(): string;
}

