<?php

namespace YouOrm\Command;

use YouConfig\Config;
use YouConsole\Command\AbstractCommand;
use YouConsole\Input\Input;
use YouConsole\Output\Output;
use YouKernel\Component\Container\Container;
use YouOrm\Connection\DBConnection;
use YouOrm\Migration\AbstractMigration;

class MigrateCommand extends AbstractCommand
{
    private DBConnection $connection;
    private Config $config;

    /**
     * @param Container $container
     */
    public function __construct(
        private readonly Container $container
    ) {
        parent::__construct();
        $this->connection = $container->get(DBConnection::class);
        $this->config = $container->get(Config::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('orm:migrate')
            ->setDescription('Execute database migrations.')
            ->addArgument('version', false, 'The specific version to migrate to.')
            ->addOption('up', 'u', false, 'Direction up.')
            ->addOption('down', 'd', false, 'Direction down.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(Input $input, Output $output): int
    {
        $targetVersion = $input->getArgument('version');
        $isForward = $input->getOption('up');
        $isBackward = $input->getOption('down');

        if ($isForward && $isBackward) {
            $output->error('Cannot specify both --up and --down.');
            return self::STATUS_ERROR;
        }

        // Default direction is up
        if (!$isForward && !$isBackward) {
            $isForward = true;
        }

        try {
            $this->ensureMigrationsTableExists();

            $migrationsPath = $this->getMigrationsPath();
            if (!is_dir($migrationsPath)) {
                $output->warning("Migrations directory not found: $migrationsPath");
                return self::STATUS_WARNING;
            }

            $discovered = discover_files($migrationsPath);
            $migrationFiles = [];
            foreach ($discovered as $path) {
                $version = $this->extractVersion($path);
                if ($version) {
                    $migrationFiles[$version] = $path;
                }
            }
            $executedVersions = $this->getExecutedVersions();

            if ($targetVersion) {
                if (!isset($migrationFiles[$targetVersion])) {
                    $output->error("Migration version '$targetVersion' not found.");
                    return self::STATUS_ERROR;
                }

                $alreadyExecuted = in_array($targetVersion, $executedVersions, true);

                if ($isForward) {
                    if ($alreadyExecuted) {
                        $output->warning("Migration '$targetVersion' is already executed.");
                        return self::STATUS_SUCCESS;
                    }
                    return $this->runMigration($targetVersion, $migrationFiles[$targetVersion], 'up', $output);
                }

                if (!$alreadyExecuted) {
                    $output->warning("Migration '$targetVersion' has not been executed yet.");
                    return self::STATUS_SUCCESS;
                }
                return $this->runMigration($targetVersion, $migrationFiles[$targetVersion], 'down', $output);
            }

            // Batch operations
            if ($isForward) {
                $pendingMigrations = array_filter($migrationFiles, static function ($version) use ($executedVersions) {
                    return !in_array($version, $executedVersions, true);
                }, ARRAY_FILTER_USE_KEY);

                if (empty($pendingMigrations)) {
                    $output->success('No pending migrations.');
                    return self::STATUS_SUCCESS;
                }

                ksort($pendingMigrations);

                if (array_any($pendingMigrations, fn($filePath, $version) => $this->runMigration($version, $filePath, 'up', $output) !== self::STATUS_SUCCESS)) {
                    return self::STATUS_ERROR;
                }

                $output->success('All pending migrations executed successfully.');
            } else {
                if (empty($executedVersions)) {
                    $output->success('Nothing to rollback.');
                    return self::STATUS_SUCCESS;
                }

                $lastVersion = end($executedVersions);
                if (!isset($migrationFiles[$lastVersion])) {
                    $output->error("Could not find file for executed migration '$lastVersion'.");
                    return self::STATUS_ERROR;
                }

                return $this->runMigration($lastVersion, $migrationFiles[$lastVersion], 'down', $output);
            }

            return self::STATUS_SUCCESS;

        } catch (\Exception $e) {
            $output->error('Migration failed: ' . $e->getMessage());
            return self::STATUS_ERROR;
        }
    }

    /**
     * @param string $version
     * @param string $filePath
     * @param string $direction
     * @param Output $output
     * @return int Status code
     */
    private function runMigration(string $version, string $filePath, string $direction, Output $output): int
    {
        if (!file_exists($filePath)) {
            $output->error("Migration file not found: $filePath");
            return self::STATUS_ERROR;
        }

        require_once $filePath;

        $className = fqcn($filePath);
        if (!$className || !class_exists($className)) {
            $output->error("Could not resolve class for migration: $filePath");
            return self::STATUS_ERROR;
        }

        if (!is_subclass_of($className, AbstractMigration::class)) {
            $output->error("Migration class $className must extend AbstractMigration.");
            return self::STATUS_ERROR;
        }

        $output->info(sprintf('%s: %s', $direction === 'up' ? 'Migrating' : 'Rolling back', $version));

        $migration = new $className($this->connection);

        $this->connection->getConnection()->beginTransaction();
        try {
            if ($direction === 'up') {
                $migration->up();
                $this->recordVersion($version);
            } else {
                $migration->down();
                $this->removeVersion($version);
            }

            // MySQL ferme automatiquement les transactions sur les DDL
            if ($this->connection->getConnection()->inTransaction()) {
                $this->connection->getConnection()->commit();
            }

            $output->success(sprintf('%s: %s', $direction === 'up' ? 'Migrated' : 'Rolled back', $version));
            return self::STATUS_SUCCESS;
        } catch (\Exception $e) {

            // MySQL ferme automatiquement les transactions sur les DDL
            if ($this->connection->getConnection()->inTransaction()) {
                $this->connection->getConnection()->rollBack();
            }

            $output->error(sprintf('Failed %s %s: %s', $direction === 'up' ? 'migrating' : 'rolling back', $version, $e->getMessage()));
            return self::STATUS_ERROR;
        }
    }

    /**
     * Ensures the migrations table exists.
     */
    private function ensureMigrationsTableExists(): void
    {
        $driver = $this->connection->getDriver();
        $tableMigrationsName = $this->config->get('database.migrations_table', 'migrations');

        $sql = match ($driver) {
            'sqlite' => "CREATE TABLE IF NOT EXISTS $tableMigrationsName (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                version VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            'pgsql' => "CREATE TABLE IF NOT EXISTS $tableMigrationsName (
                id SERIAL PRIMARY KEY,
                version VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            'sqlsrv' => "CREATE TABLE IF NOT EXISTS $tableMigrationsName (
                id INT IDENTITY(1,1) PRIMARY KEY,
                version VARCHAR(255) NOT NULL,
                executed_at DATETIME DEFAULT GETDATE()
            )",
            default => "CREATE TABLE IF NOT EXISTS $tableMigrationsName (
                id INT AUTO_INCREMENT PRIMARY KEY,
                version VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        };

        $this->connection->getConnection()->exec($sql);
    }

    /**
     * @return string
     */
    private function getMigrationsPath(): string
    {
        $projectDir = $this->container->get('project_dir');
        $relative = $this->config->get('database.migrations_path', 'migrations');

        return sprintf('%s/%s', $projectDir, ltrim($relative, DIRECTORY_SEPARATOR));
    }

    /**
     * @return array<string>
     */
    private function getExecutedVersions(): array
    {
        $stmt = $this->connection->getConnection()->query("SELECT version FROM migrations ORDER BY id ASC");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param string $filePath
     * @return string|null
     */
    private function extractVersion(string $filePath): ?string
    {
        return pathinfo($filePath, PATHINFO_FILENAME);
    }

    /**
     * @param string $version
     */
    private function recordVersion(string $version): void
    {
        $sql = "INSERT INTO migrations (version) VALUES (:version)";
        $stmt = $this->connection->getConnection()->prepare($sql);
        $stmt->execute(['version' => $version]);
    }

    /**
     * @param string $version
     */
    private function removeVersion(string $version): void
    {
        $sql = "DELETE FROM migrations WHERE version = :version";
        $stmt = $this->connection->getConnection()->prepare($sql);
        $stmt->execute(['version' => $version]);
    }

}
