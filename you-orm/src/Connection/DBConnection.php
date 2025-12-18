<?php

namespace YouOrm\Connection;

use PDO;
use PDOException;

/**
 * Class DBConnection
 *
 * Manages database connection lifecycle following SRP principles.
 * Responsibilities:
 * - Create and maintain a single PDO connection
 * - Provide access to the PDO instance
 * - Handle connection state validation
 */
class DBConnection
{
    private ?PDO $connection = null;
    private ConnectionConfig $config;

    /**
     * @param ConnectionConfig $config Database configuration
     */
    public function __construct(ConnectionConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Establish database connection.
     *
     * @throws PDOException If connection fails
     */
    public function connect(): void
    {
        if ($this->connection !== null) {
            return; // Already connected
        }

        try {
            $this->connection = new PDO(
                $this->config->getDsn(),
                $this->config->getUsername(),
                $this->config->getPassword(),
                $this->config->getOptions()
            );
        } catch (PDOException $e) {
            throw new PDOException(
                sprintf("Connection failed: %s", $e->getMessage()),
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get the active PDO connection.
     *
     * @return PDO
     * @throws \RuntimeException If not connected
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            throw new \RuntimeException(
                "Database connection has not been established. Call connect() first."
            );
        }

        return $this->connection;
    }

    /**
     * Get the database configuration.
     *
     * @return ConnectionConfig
     */
    public function getConfig(): ConnectionConfig
    {
        return $this->config;
    }

    /**
     * Check if connection is active.
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connection !== null;
    }

    /**
     * Close the database connection.
     */
    public function disconnect(): void
    {
        $this->connection = null;
    }
}