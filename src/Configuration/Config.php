<?php

namespace JDS\Configuration;

use JDS\Configuration\ConfigInterface;

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

    public function __construct(private array $config = [])
    {
        // validate and ensure the environment key has a valid value, or set a default
        $this->config['environment'] = $this->validateEnvironment($this->config['environment'] ?? null);

        // set the environment property
        $this->environment = $this->config['environment'];

        // set the appropriate log level based on the environment
        $this->logLevel = $this->determineLogLevel();

        // validate database configuration
        $this->validateDatabaseConfig($this->config['db'] ?? []);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

//    public function get2and3(mixed $data, ?string $key, ?string $key1, mixed $default = null): mixed
//    {
//        // if $data is not an array, return it directly
//        if (!is_array($data)) {
//            return $data;
//        }
//
//        // if data is an array, proceed with the lookup
//        foreach ($data as $k => $v) {
//            // if $v is not an array, return $k
//            if (!is_array($v)) {
//                return $v;
//            }
//            // if $v is an array, look for $key1 inside it
//            if ($k === $key && isset($v[$key1])) {
//                return $v[$key1];
//            }
//        }
//        return $default;
//    }


    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->config);
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
            'development' => 'DEBUG',
            'testing' => 'WARNING',
            default => 'ERROR'
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

    private function validateDatabaseConfig(array $config): void
    {
        $requiredKeys = ['driver', 'dbname', 'host', 'user', 'password', 'port'];
        $missingKeys = array_diff($requiredKeys, array_keys($config));
        if (!empty($missingKeys)) {
            throw new ConfigurationException("Missing required database configuration keys: " . implode(', ', $missingKeys));
        }
  // redundant
    }
}

