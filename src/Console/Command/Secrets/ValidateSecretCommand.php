<?php

namespace JDS\Console\Command\Secrets;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Security\SecretsValidator;

class ValidateSecretCommand implements CommandInterface
{
    public function __construct(
        private readonly string $schemaPath,
        private readonly string $plainPath
    )
    {
    }

    public function execute(array $params = []): int
    {
        if (!is_file($this->plainPath)) {
            fwrite(STDERR, "Plain secrets file not found: {$this->plainPath}" . PHP_EOL);
            return 1;
        }

        if (!is_file($this->schemaPath)) {
            fwrite(STDERR, "Schema file not found: {$this->schemaPath}" . PHP_EOL);
            return 1;
        }

        $secrets = json_decode(file_get_contents($this->plainPath), true);
        $schema = json_decode(file_get_contents($this->schemaPath), true);

        if (!is_array($secrets)) {
            fwrite(STDERR, "Invalid secrets schema." . PHP_EOL);
            return 1;
        }

        if (!is_array($schema)) {
            fwrite(STDERR, "Invalid secrets schema." . PHP_EOL);
            return 1;
        }

        try {
            (new SecretsValidator($schema))->validate($secrets);
        } catch (Throwable $e) {
            fwrite(STDERR, "Validation failed: {$e->getMessage()}" . PHP_EOL);
            return 1;
        }

        fwrite(STDOUT, "Secrets validated successfully." . PHP_EOL);
        return 0;
    }
}

