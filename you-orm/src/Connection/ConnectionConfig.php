<?php

namespace YouOrm\Connection;

use PDO;

/**
 * Class ConnectionConfig
 *
 * Value object encapsulating database connection configuration.
 * Responsibilities:
 * - Store connection parameters
 * - Provide immutable access to configuration
 * - Define default PDO options
 */
class ConnectionConfig
{
    private string $dsn;
    private ?string $username;
    private ?string $password;
    private array $options;

    /**
     * @param string $dsn Database DSN
     * @param string|null $username Database username
     * @param string|null $password Database password
     * @param array $options PDO options
     */
    public function __construct(
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        array $options = []
    ) {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options = $this->mergeDefaultOptions($options);
    }

    /**
     * Create config from environment or array.
     *
     * @param array $params Configuration parameters
     * @return self
     */
    public static function fromArray(array $params): self
    {
        return new self(
            $params['dsn'] ?? '',
            $params['username'] ?? null,
            $params['password'] ?? null,
            $params['options'] ?? []
        );
    }

    public function getDsn(): string
    {
        return $this->dsn;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Merge user options with defaults.
     *
     * @param array $options User-provided options
     * @return array
     */
    private function mergeDefaultOptions(array $options): array
    {
        $defaults = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch results as associative arrays
            PDO::ATTR_EMULATE_PREPARES => false, // Don't emulate prepared statements
        ];

        return array_replace($defaults, $options);
    }
}