<?php

namespace JDS\Console\Command\Secrets;

use JDS\Console\BaseCommand;
use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Security\SecretsValidator;
use Throwable;

class ValidateSecretCommand extends BaseCommand implements CommandInterface
{
    protected string $name = 'secrets:validate';
    protected string $description = 'Validate plaintext secrets against the JSON schema.';

    public function __construct(
        private readonly string $schemaPath,
        private readonly string $plainPath
    )
    {
    }

    public function execute(array $params = []): int
    {
        if (!is_file($this->plainPath)) {
            $file = basename($this->plainPath, '.json');
            $this->error("Plain secrets file not found: {$file}. [Validate:Secrets:Command]");
            return 1;
        }

        if (!is_file($this->schemaPath)) {
            $file = basename($this->schemaPath, '.json');
            $this->error("Schema file not found: {$file}. [Validate:Secrets:Command].");
            return 1;
        }

        $secrets = json_decode(file_get_contents($this->plainPath), true);
        $schema = json_decode(file_get_contents($this->schemaPath), true);

        if (!is_array($secrets)) {
            $this->error("Invalid secrets file. [Validate:Secrets:Command].");
            return 1;
        }

        if (!is_array($schema)) {
            $this->error("Invalid secrets schema. [Validate:Secrets:Command].");
            return 1;
        }

        try {
            (new SecretsValidator($schema))->validate($secrets);
        } catch (Throwable $e) {
            $this->error("Validation failed: {$e->getMessage()}. [Validate:Secrets:Command].");
            return 1;
        }

        $this->writeln("Secrets validated successfully.");
        return 0;
    }
}

