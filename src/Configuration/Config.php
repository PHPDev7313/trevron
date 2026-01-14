<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2026 Jessop Digital Systems
 * Date: January 5, 2026
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapLifecycleAndInvariants.v1.2.FINAL.md
 *    : ConsoleBootstrapLifecycle.v1.2.2.FINAL.md
 */

namespace JDS\Configuration;

use JDS\Contracts\Configuration\ConfigInterface;
use RuntimeException;

class Config implements ConfigInterface
{

    /**
     * The environment in which the application or code is running.
     * This variable typically defines configuration or behavior differences,
     * such as 'development', 'staging', 'testing', or 'production'.
     */
    private string $environment;

    /**
     * Represents the severity level of logging.
     * This variable indicates the threshold for log messages,
     * such as 'debug', 'info', 'warning', or 'error'.
     */
    private string $logLevel;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(private array $config = [])
    {
        // validate and ensure the environment key has a valid value, or set a default
        $env = $config['app']["environment"] ?? null;

        $this->environment = $this->validateEnvironment($env);

        // set the appropriate log level based on the environment
        $this->logLevel = $this->determineLogLevel();
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        //
        // Fast path for direct root-level key
        //
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        //
        // Dot-notation support
        //
        $segments = explode('.', $key);
        $value = $this->config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];

        }
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        //return $this->get($key, '__not_found__') !== '__not found___'; //array_key_exists($key, $this->config);

        $segments = explode('.', $key);
        $value = $this->config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return false;
            }

            $value = $value[$segment];
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return $this->config;
    }

    private function validateEnvironment(?string $env): string
    {
        // list of allowed environments
        $allowedEnvValues = ['production', 'development', 'testing', 'staging'];
        return in_array($env, $allowedEnvValues, true) ? $env : 'production';
    }

    private function determineLogLevel(): string
    {
        return match ($this->environment) {
            'production', 'staging' => 'ERROR',
            'development'           => 'DEBUG',
            'testing'               => 'WARNING',
            default                 => 'ERROR',
        };
    }

    /**
     * Gets the current log level of the system.
     *
     * @return string The log level.
     */
    public function getLogLevel(): string
    {
        return $this->logLevel;
    }

    /**
     * Retrieves the current environment setting.
     *
     * @return string The environment configuration value.
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Determines if the current environment is set to production.
     *
     * @return bool True if the environment is production, false otherwise.
     */
    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    /**
     * Determines if the current environment is set to development mode.
     *
     * @return bool True if the environment is development, otherwise false.
     */
    public function isDevelopment(): bool
    {
        return $this->environment === 'development';
    }

    /**
     * Determines if the current environment is set to 'testing'.
     *
     * @return bool True if the environment is 'testing', otherwise false.
     */
    public function isTesting(): bool
    {
        return $this->environment === 'testing';
    }

    /**
     * Determines if the current environment is set to 'staging'.
     *
     * @return bool True if the environment is 'staging', otherwise false.
     */
    public function isStaging(): bool
    {
        return $this->environment === 'staging';
    }

    /**
     * Always returns an array.
     */
    public function getArray(string $key, array $default = []): array
    {
        $value = $this->get($key, $default);

        if ($value === null) {
            return $default;
        }

        if (is_array($value)) {
            return $value;
        }

        return [$value];
    }

    /**
     * Returns the first value of an array-or-scalar config entry.
     */
    public function getFirst(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);

        if (is_array($value)) {
            return $value[0] ?? $default;
        }

        return $value;
    }

    /**
     * Returns the Domain Specific Twig Template Root
     */
    public function twigTemplateRoot(): string
    {
        $paths = $this->getArray('twig.templates.paths');

        if ($paths === []) {
            throw new RuntimeException(
                'No twig template paths configured. [Config].'
            );
        }

        return (string) $paths[0];
    }
}

