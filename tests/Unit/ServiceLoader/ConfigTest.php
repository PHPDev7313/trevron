<?php


use JDS\Configuration\Config;
use Psr\Log\LogLevel;

it('constructs config with minimal environment', function () {
    $config = new Config([
        'environment' => 'development',
    ]);

    expect($config->getEnvironment())->toBe('development')
        ->and($config->getLogLevel())->toBe('DEBUG');
});

it('falls back to production when environment is missing or invalid', function () {
    $config = new Config([]);

    expect($config->getEnvironment())->toBe('production')
        ->and($config->getLogLevel())->toBe('ERROR');
});

it('retrieves flat keys corretly', function () {
    $config = new Config([
        'appPath' => '/var/www/app',
        'debug' => true,
    ]);

    expect($config->get('appPath'))->toBe('/var/www/app')
        ->and($config->get('debug'))->toBeTrue();
});


it('retrieves nested keys using dot notation', function () {
    $config = new Config([
        'secrets' => [
            'file' => 'config/secrets.json.enc',
            'plain' => 'config/secrets.plain.json',
            'schema' => 'config/secrets.schema.json',
        ],
        'app' => [
            'secret' => [
                'key' => 'base64:XYZ',
            ]
        ],
    ]);

    expect($config->get('secrets.file'))
        ->toBe('config/secrets.json.enc')
        ->and($config->get('app.secret.key'))
        ->toBe('base64:XYZ');
});

it('returns default when flat or nested keys do not exist', function () {
    $config = new Config([
        'appPath' => '/var/www/app',
    ]);

    expect($config->get('missing', 'default-value'))
        ->toBe('default-value')
        ->and($config->get('app.route.prefix', 'fallback'))
        ->toBe('fallback');
});

it('has() correctly identifies flat and nested keys', function () {
    $config = new Config([
        'db' => [
            'host' => 'localhost',
        ],
    ]);

    expect($config->has('db'))->toBeTrue();
    expect($config->has('db.host'))->toBeTrue();
    expect($config->has('db.port'))->toBeFalse();
    expect($config->has('not.real'))->toBeFalse();
});

it('accurately identifies flat, nested, missing, null, and edge-case keys using has()', function () {

    $config = new Config([
        'db' => [
            'host' => 'localhost',
            'port' => null,               // null value
            'flags' => [
                'readonly' => false,      // false value
                'replicas' => [
                    'r1' => '10.0.0.1',
                ],
            ],
        ],
        'app' => [
            'secret' => [
                'key' => 'abc123'
            ],
        ],
        'simple' => true,                 // flat non-nested key
        'nullKey' => null,                // explicit null
        'notArray' => 'string-value',     // parent that is NOT an array
    ]);

    // --- FLAT KEYS ---
    expect($config->has('simple'))->toBeTrue();     // exists
    expect($config->has('nullKey'))->toBeTrue();    // exists even though value is null
    expect($config->has('missingFlat'))->toBeFalse();

    // --- ONE LEVEL NESTED ---
    expect($config->has('db'))->toBeTrue();
    expect($config->has('db.host'))->toBeTrue();
    expect($config->has('db.port'))->toBeTrue();    // exists even though value is null

    // --- TWO LEVELS ---
    expect($config->has('db.flags'))->toBeTrue();
    expect($config->has('db.flags.readonly'))->toBeTrue();  // false value still counts as exists

    // --- THREE LEVELS ---
    expect($config->has('db.flags.replicas.r1'))->toBeTrue();
    expect($config->has('db.flags.replicas.r2'))->toBeFalse();

    // --- MISSING NESTED KEYS ---
    expect($config->has('db.user'))->toBeFalse();
    expect($config->has('db.host.name'))->toBeFalse();

    // --- PARENT EXISTS BUT IS NOT AN ARRAY ---
    expect($config->has('notArray.key'))->toBeFalse();  // cannot descend into a string

    // --- DEEP NON-EXISTENT CHAIN ---
    expect($config->has('app.secret.key'))->toBeTrue();
    expect($config->has('app.secret.missing'))->toBeFalse();
    expect($config->has('app.secret.key.extra'))->toBeFalse();

    // --- TOTAL NON-EXISTENT PATHS ---
    expect($config->has('nothing.at.all'))->toBeFalse();
});

it('returns all config data using all()', function () {
    $data = [
        'env' => 'development',
        'db' => ['host' => 'localhost'],
    ];

    $config = new Config($data);
    expect($config->all())->toBe($data);
});

it('validates environment and sets default when invalid', function () {
    $config = new Config(['environment' => 'invalid-env']);

    expect($config->getEnvironment())->toBe('production');
});

it('determines correct log level based on environment', function () {
    expect((new Config(['environment' => 'production']))->getLogLevel())->toBe('ERROR');

    expect((new Config(['environment' => 'staging']))->getLogLevel())->toBe('ERROR');

    expect((new Config(['environment' => 'development']))->getLogLevel())->toBe('DEBUG');

    expect((new Config(['environment' => 'testing']))->getLogLevel())->toBe('WARNING');

});

it('supports deep nested structures beyond two levels', function () {
    $config = new Config([
        'level1' => [
            'level2' => [
                'level3' => [
                    'key' => 'deep-value'
                ],
            ],
        ],
    ]);

    expect($config->get('level1.level2.level3.key'))->toBe('deep-value');
});




