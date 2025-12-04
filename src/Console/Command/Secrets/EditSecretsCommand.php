<?php

namespace JDS\Console\Command\Secrets;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Security\SecretsCrypto;
use JDS\Security\SecretsManager;

class EditSecretsCommand implements CommandInterface
{
    public function __construct(
        private readonly string $appSecretKey,
        private readonly string $plainPath,
        private readonly string $encPath
    )
    {
    }

    public function execute(array $params = []): int
    {
        $editor = $_ENV['EDITOR'] ?: 'nano';

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
            file_put_contents($this->plainPath, json_encode([
                "db" => ["user" => "", "password" => ""],
                "jwt" => ["access" => "", "refresh" =>"", "token" => ""],
                "encryption" => ["key" => "", "crypt" => "", "appSecret" => ""]
            ], JSON_PRETTY_PRINT));
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

        fwrite(STDOUT, "Secrets updated." . PHP_EOL);
        return 0;
    }
}

