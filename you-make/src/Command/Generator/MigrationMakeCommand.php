<?php

namespace YouMake\Command\Generator;

use Random\RandomException;
use YouConfig\Config;
use YouConsole\Input\Input;
use YouConsole\Output\Output;
use YouOrm\Grammar\DDL\ {
    MySqlGrammarDDL,
    PostgreSqlGrammarDDL,
    SqliteGrammarDDL,
    SqlServerGrammarDDL
};
use YouConsole\Output\OutputStyle;
use YouOrm\Connection\DBConnection;
use YouOrm\Discovery\EntityDiscovery;
use YouOrm\Migration\MigrationGenerator;
use YouOrm\Schema\Entity\EntitySchemaReader;
use YouOrm\Schema\Introspector\DatabaseSchemaIntrospectorInterface;
use YouOrm\Schema\Introspector\MySqlIntrospector;
use YouOrm\Schema\Introspector\PostgreSqlIntrospector;
use YouOrm\Schema\SchemaComparator;

/**
 * Commande pour générer une migration.
 */
class MigrationMakeCommand extends AbstractGeneratorCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:migration')
            ->setDescription('Génère une nouvelle migration');
    }

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../Resources/stubs/migration.stub';
    }

    /**
     * @param Input $input
     * @param Output $output
     * @return int
     * @throws \ReflectionException
     */
    protected function execute(Input $input, Output $output): int
    {
        $className = $this->getClassName();

        $path = $this->getDestinationPath($className);

        if (file_exists($path)) {
            $output->error("Le fichier existe déjà : $path");
            return self::STATUS_ERROR;
        }

        $this->makeDirectory($path);

        $content = $this->buildClass($className);

        if (file_put_contents($path, $content) === false) {
            $output->error("Impossible d'écrire le fichier : $path");
            return self::STATUS_ERROR;
        }

        $output->success("Migration SQL generated successfully : $path");

        return self::STATUS_SUCCESS;
    }

    /**
     * @param string $className
     * @return string
     * @throws \ReflectionException
     * @throws RandomException
     */
    protected function getDestinationPath(string $className): string
    {
        $config = $this->container->get(Config::class);
        $projectDir = $this->container->get('project_dir');

        $migrationsPath = $projectDir . '/' . ltrim($config->get('database.migrations_path', 'migrations'), '/');

        return sprintf('%s/%s.php', $migrationsPath, $className);
    }

    private function getEntitiesPath(): string
    {
        $config = $this->container->get(Config::class);
        $projectDir = $this->container->get('project_dir');

        return $projectDir . '/' . ltrim($config->get('database.entities_path', 'src/Entity'), '/');
    }

    /**
     * @param string $className
     * @return array<string, string>
     */
    protected function getReplacements(string $className): array
    {
        $replacements = parent::getReplacements($className);

        /**
         * @var Config $config
         */
        $config = $this->container->get(Config::class);

        $driver = $config->get('database.driver', 'mysql');

        // 1. Setup Discovery
        $discovery = new EntityDiscovery();
        $reader = new EntitySchemaReader($discovery);

        // 2. Read Schema from Entities
        $newSchema = $reader->read($this->getEntitiesPath());

        if (count($newSchema->getTables()) === 0) {
            echo OutputStyle::apply('error', '[FAIL] No tables found in schema.\\n');
            exit(1);
        }

        // 3. Create Old Schema (From database)
        /**
         * @var DBConnection $connection
         */
        $connection = $this->container->get(DBConnection::class);

        /**
         * @var DatabaseSchemaIntrospectorInterface $introspector
         */
        $introspector = match ($driver) {
            'pgsql' => new PostgreSqlIntrospector($connection),
            default =>  new MySqlIntrospector($connection),
        };

        $oldSchema = $introspector->introspect();

        // 4. Compare Schemas
        $comparator = new SchemaComparator();
        $diff = $comparator->compare($oldSchema, $newSchema);

        if (!$diff->hasChanges()) {
            echo OutputStyle::apply('error', '[FAIL] No changes detected.\\n');
            exit(1);
        }

        // 5. Generate Migration SQL
        $grammar = match ($driver) {
            'pgsql' => new PostgreSqlGrammarDDL(),
            'sqlite' => new SqliteGrammarDDL(),
            'sqlsrv' => new SqlServerGrammarDDL(),
            default => new MySqlGrammarDDL(),
        };

        $generator = new MigrationGenerator($grammar, $discovery);

        $migrationSql = $generator->generateDiff($diff);

        $replacements['{{{ up }}'] = $migrationSql['up'];
        $replacements['{{ down }}'] = $migrationSql['down'];
        return $replacements;
    }

    /**
     * Retourne le nom de la classe.
     *
     * @param string $name
     * @return string
     */
    protected function getClassName(string $name = 'Version'): string
    {
        return sprintf('%s_%s_%s', $name, date('Y_m_d_His_'), random_int(100000, 999999));
    }
}