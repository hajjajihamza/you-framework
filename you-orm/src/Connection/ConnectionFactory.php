<?php

namespace YouOrm\Connection;

/**
 * Class ConnectionFactory
 *
 * Factory for creating configured database connections.
 * Responsibilities:
 * - Abstract connection creation logic
 * - Provide convenient creation methods
 */
class ConnectionFactory
{
    /**
     * Create a MySQL connection.
     *
     * @param string $host
     * @param string $port
     * @param string $database
     * @param string $username
     * @param string $password
     * @param string $charset
     * @param array $options
     * @return DBConnection
     */
    public static function createMySQLConnection(
        string $host,
        string $database,
        string $username,
        string $password,
        string $port = '3306',
        string $charset = 'utf8mb4',
        array $options = []
    ): DBConnection {
        $dsn = sprintf(
            "mysql:host=%s:%s;dbname=%s;charset=%s",
            $host,
            $port,
            $database,
            strtoupper($charset)
        );

        $config = new ConnectionConfig($dsn, $username, $password, $options);
        return new DBConnection($config);
    }

    /**
     * Create a PostgreSQL connection.
     *
     * @param string $host
     * @param string $port
     * @param string $database
     * @param string $username
     * @param string $password
     * @param string $charset
     * @param array $options
     * @return DBConnection
     */
    public static function createPostgreSQLConnection(
        string $host,
        string $database,
        string $username,
        string $password,
        string $port = '5432',
        string $charset = 'utf8mb4',
        array $options = []
    ): DBConnection {
        $dsn = sprintf(
            "pgsql:host=%s:%s;dbname=%s;options='--client_encoding=%s'",
            $host,
            $port,
            $database,
            strtoupper($charset)
        );

        $config = new ConnectionConfig($dsn, $username, $password, $options);
        return new DBConnection($config);
    }

    /**
     * Create a SQL Server connection.
     *
     * @param string $host
     * @param string $database
     * @param string $username
     * @param string $password
     * @param string $port
     * @param string $charset
     * @param array $options
     * @return DBConnection
     */
    public static function createSQLServerConnection(
        string $host,
        string $database,
        string $username,
        string $password,
        string $port = '1433',
        string $charset = 'UTF-8',
        array $options = []
    ): DBConnection {
        $dsn = sprintf(
            "sqlsrv:Server=%s,%s;Database=%s;CharacterSet=%s",
            $host,
            $port,
            $database,
            strtoupper($charset)
        );

        $config = new ConnectionConfig($dsn, $username, $password, $options);
        return new DBConnection($config);
    }

    /**
     * Create a SQLite connection.
     *
     * @param string $path
     * @param array $options
     * @return DBConnection
     */
    public static function createSQLiteConnection(string $path, array $options = []): DBConnection
    {
        $dsn = sprintf("sqlite:%s", $path);

        $config = new ConnectionConfig($dsn, '', '', $options);
        return new DBConnection($config);
    }


    /**
     * Create connection from configuration.
     *
     * @param ConnectionConfig $config
     * @return DBConnection
     */
    public static function create(ConnectionConfig $config): DBConnection
    {
        return new DBConnection($config);
    }

    /**
     * Create a connection from a configuration array.
     *
     * @param array $config Configuration array (e.g., from config/database.php)
     * @return DBConnection
     * @throws \InvalidArgumentException If driver is unsupported or configuration is invalid
     */
    public static function createFromConfig(array $config): DBConnection
    {
        $default = $config['default'] ?? 'mysql';
        $connections = $config['connections'] ?? [];

        if (!isset($connections[$default])) {
            throw new \InvalidArgumentException("Database connection [{$default}] not configured.");
        }

        $params = $connections[$default];
        $driver = $params['driver'] ?? 'mysql';
        $options = $params['options'] ?? [];

        return match ($driver) {
            'mysql' => self::createMySQLConnection(
                $params['host'] ?? '127.0.0.1',
                $params['database'] ?? '',
                $params['username'] ?? 'root',
                $params['password'] ?? '',
                $params['port'] ?? '3306',
                $params['charset'] ?? 'utf8mb4',
                $options
            ),
            'pgsql' => self::createPostgreSQLConnection(
                $params['host'] ?? '127.0.0.1',
                $params['database'] ?? '',
                $params['username'] ?? 'root',
                $params['password'] ?? '',
                $params['port'] ?? '5432',
                $params['charset'] ?? 'utf8',
                $options
            ),
            'sqlsrv' => self::createSQLServerConnection(
                $params['host'] ?? 'localhost',
                $params['database'] ?? '',
                $params['username'] ?? 'root',
                $params['password'] ?? '',
                $params['port'] ?? '1433',
                $params['charset'] ?? 'utf8',
                $options
            ),
            'sqlite' => self::createSQLiteConnection(
                $params['database'] ?? ':memory:',
                $options
            ),
            default => throw new \InvalidArgumentException("Unsupported database driver [{$driver}]."),
        };
    }
}