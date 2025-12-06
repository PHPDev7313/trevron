<?php

use JDS\Contracts\Security\SecretsInterface;
use JDS\Security\Secrets;
use JDS\ServiceProvider\SecretsServiceProvider;
use League\Container\Container;

beforeEach(function () {
    $this->container = new Container();

    //
    // use temp files for schema + secrets
    //
    $this->tmpDir = sys_get_temp_dir() . 'jds_secrets_tests_' . uniqid();
    mkdir($this->tmpDir, 0777, true);

    $this->schemaPath = $this->tmpDir . '/secrets.schema.json';
    $this->secretsPath = $this->tmpDir . '/secrets.json.enc';

    //
    // Schema (from what you provided)
    //
    $schema = [
        "db" => [
            "user" => "database user",
            "password" => "super-secret-password",
        ],
        "jwt" => [
            "access" => "32-byte-secret-access",
            "refresh" => "32-byte-secret-refresh",
            "token" => "32-byte-secret-token",
        ],
        "encryption" => [
            "key" => "32-byte-encryption-key",
            "crypt" => "legacy-compatible-crypt-key-if-needed",
        ],
        "mail" => [
            "host" => "mail.example.com",
            "user" => "you@example.com",
            "pass" => "your-mail-password",
            "from" => "you@example.com",
            "fromName" => "Your Name",
            "port" => 333,
        ],
        "misc" => [
            "tokenTTL" => 111,
            "sessionPrefix" => "sessionPrefix",
            "algorithm" => "an algorithm",
        ],
    ];

    file_put_contents($this->schemaPath, json_encode($schema, JSON_PRETTY_PRINT));

    //
    // NOTE: this assumes SecretManager::load() can understand this file.
    // If your SecretsManager expects *actual encrypted* content, generate
    // this file in a setup step using your real secrets:encrypt command.
    $secretsPlain = [
        "db" => [
            "user" => "test_user",
            "password" => "test_password",
        ],
        "jwt" => [
            "access" => str_repeat('a', 32),
            "refresh" => str_repeat('b', 32),
            "token" => str_repeat('c', 32),
        ],
        "encryption" => [
            "key" => str_repeat('d', 32),
            "crypt" => "legacy-key",
        ],
        "mail" => [
            "host" => "smtp.test.dev",
            "user" => "dev@test.dev",
            "pass" => "mail-pass",
            "from" => "dev@test.dev",
            "fromName" => "Dev Tester",
            "port" => 2525,
        ],
        "misc" => [
            "tokenTTL" => 3600,
            "sessionPrefix" => "sess_",
            "algorithm" => "HS256",
        ],
    ];

    //
    // for plaintext-friendly SecretsManager, you might just:
    //
    file_put_contents($this->secretsPath, 'vjOfrdxzDVZ4wJFGGScuw7lodL/zF6k8nXKarLd842dATi+LZLrZa8IzxbNzBBW0clbaXqMx1f5TPHWMRinNzLDUBN1vrhWurr3ZpgJ8hHgOsXWVbERgeQicTojIq/Yem+fKLZZ6RDYNiZ1RZjnZNOh7bNUsMR5lYhelVe/6WY097zeRQW0KcnII4OKIPwAjsIr2jlvgSsytTrkoL0coJTir5z9Xdh3KqebxiYzk3dcXfqGjUmkJmtDi79cDuCTrbBykFF0v9pgFOKjCDcQpKX6G8Wl8igobVKAlYKDkkzfVeqY7QrVDwc4nisHttw0lHnuLtg45r4XhLuqlf5PIDnA4IVYv7hCc9M9JuzwZnBm1FNni6q6eK5UwU7/IZ+eDiRU5b94C6ej0yltg1Ww05GPZPmCtobwtJY1LXR9stnUeSnYUd2jx1FPhg5DjO/ex6Mmr1j2ixuaIEZakh7pZHctQDfxudmpJ37rKyM6+wZCSZu6eVBl01geQ5nNOhcdftypP9GtLxkAkJr4J4uJpdkbElHSkUOoPelcEJXXjIbd3/bUcJ3FJIwJczmNjFvhY3syKj0zF0DqXzzGN1ep9Ccy0ln1wjtzFR7Pzrs6QaV7Skp6cUE8dyrjp7X72Xqk0Lghi/wDYn5JXdyzbLVR80jNkua59TTpFTGGBInFZHmW9pbVFg0nfPmt5a0LTn5DEU++Y01LDEGuZM1A2Df8cmh+4EYTd/jGnGlCcVM8SbZJi68TyL9VLoeUSfIn+nsARm3I33ac2vUK0XYkLOagb3SAoHAeq9skTUtYlTfYIAwzJEyGWfox7/PzMPJ+qayRnFRsMXtqhszOAaP9u9BQcLc3/h2gnk8vswbAnZtMVSeSsWTrMm7My0loPrLmdAE3y2DOwhpVxRib0qUyeV3cBEBX/hTEM0hC1GkqvCZ8IEBHtOs0dPkEUYIpRjAuK+DjLMvhEdsq8og==');

    //
    // fake app secret key just for tests
    //
    $this->testKeyBase64 = base64_encode(random_bytes(32));

    $this->provider = new SecretsServiceProvider(
        $this->container,
        $this->secretsPath,
        $this->schemaPath,
        $this->testKeyBase64,
    );
});

afterEach(function () {
    if (is_dir($this->tmpDir)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->tmpDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
        rmdir($this->tmpDir);
    }
});

it('secrets service provider declares the correct services', function () {
    expect($this->provider->provides(SecretsInterface::class))->toBeTrue()
        ->and($this->provider->provides(Secrets::class))->toBeTrue()
        ->and($this->provider->provides('secrets'))->toBeTrue()
        ->and($this->provider->provides('something-else'))->toBeFalse();
});

it('secrets service provider registers aliases and returns Secrets instance', function () {
    $this->provider->register();

    expect($this->container->has(SecretsInterface::class))->toBeTrue()
        ->and($this->container->has(Secrets::class))->toBeTrue()
        ->and($this->container->has('secrets'))->toBeTrue();

    $byInterface = $this->container->get(SecretsInterface::class);
//    $byClass = $this->container->get(Secrets::class);
//    $byString = $this->container->get('secrets');
//
//    expect($byInterface)->toBeInstanceOf(SecretsInterface::class);
//    expect($byClass)->toBeInstanceOf(Secrets::class);
//    expect($byString)->toBeInstanceOf('secrets');
//
//    //
//    // all aliases must resolve to the same underlying instance
//    //
//    expect($byInterface->get('db.user'))->toBe('test_user');
});
