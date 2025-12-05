<?php

namespace JDS\Console\Command\Secrets;

use JDS\Console\Command\BaseCommand;
use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Security\SecretsCrypto;
use JDS\Security\SecretsManager;

class EditSecretsCommand extends BaseCommand implements CommandInterface
{
    protected string $name = 'secrets:edit';
    protected string $description = 'Decrypt, edit, and re-encrypt secrets using $EDITOR or OS-aware default.';

    public function __construct(
        private readonly string $appSecretKey,
        private readonly string $plainPath,
        private readonly string $encPath
    )
    {
    }

    public function execute(array $params = []): int
    {
        $editor = $this->resolveEditor(); //$_ENV['EDITOR'] ?: 'notepad';

        //
        // decrypt -> write plaintext temp file
        //
        if (is_file($this->encPath)) {
            $crypto = SecretsCrypto::fromBase64($this->appSecretKey);
            $manager = new SecretsManager($this->encPath, $crypto);
            $secrets = $manager->load();
            file_put_contents($this->plainPath, json_encode($secrets, JSON_PRETTY_PRINT));
        } else {
            //
            // create new empty template
            //
            file_put_contents(
                $this->plainPath,
                json_encode([
                    "db" => ["user" => "", "password" => ""],
                    "jwt" => ["access" => "", "refresh" =>"", "token" => ""],
                    "encryption" => ["key" => "", "crypt" => ""]
                ], JSON_PRETTY_PRINT)
            );
        }

        //
        // open editor
        //
        system("$editor {$this->plainPath}");

        // re-encrypt
        $json = file_get_contents($this->plainPath);
        $secrets = json_decode($json, true);

        $crypto = SecretsCrypto::fromBase64($this->appSecretKey);
        $manager = new SecretsManager($this->encPath, $crypto);
        $manager->save($secrets);

        $this->writeln("Secrets updated. [Edit:Secrets:Command]");
        return 0;
    }

    private function resolveEditor(): string
    {
        //
        // 1. Framework override (highest priority)
        //
        $frameworkEditor = $_ENV['FRAMEWORK_EDITOR'] ?? null;
        if ($frameworkEditor) {
            return $frameworkEditor;
        }

        //
        // 2. System EDITOR variable (common on linux/macOS)
        //
        $envEditor = $_ENV['EDITOR'] ?? null;
        if ($envEditor) {
            return $envEditor;
        }

        //
        // 3. OS-aware fallback
        //
        return match (PHP_OS_FAMILY) {
            'Windows' => 'notepad',
            'Darwin', 'Linux' => 'nano',
            default => 'notepad'
        };
    }
}

