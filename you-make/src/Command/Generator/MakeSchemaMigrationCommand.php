<?php

namespace YouMake\Command\Generator;

use Symfony\Component\Console\Input\InputOption;
use YouOrm\Connection\ConnectionFactory;
use YouOrm\Discovery\EntityDiscovery;
use YouOrm\Grammar\DDL\MySqlGrammarDDL;
use YouOrm\Grammar\DDL\PostgreSqlGrammarDDL;
use YouOrm\Grammar\DDL\SqliteGrammarDDL;
use YouOrm\Grammar\DDL\SqlServerGrammarDDL;
use YouOrm\Migration\MigrationGenerator;

class MakeSchemaMigrationCommand extends AbstractGeneratorCommand
{
    protected function configure(): void
    {
        $this->setName('make:migration:schema')
            ->setDescription('Generate a migration from existing entities')
            ->addArgument('name', true, 'The name of the migration (e.g. create_schema)')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Path to entities', ['src/Entity']);
        // Default path assumes src/Entity exists. User can override.
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../../Resources/stubs/migration_schema.stub';
    }

    protected function getDestinationPath(string $name): string
    {
        $timestamp = date('Y_m_d_His');
        return getcwd() . '/database/migrations/' . $timestamp . '_' . $name . '.php';
    }

    protected function getReplacements(string $name): array
    {
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

        // Load configuration to determine driver
        $configPath = getcwd() . '/config/database.php';
        $driver = 'mysql'; // Default

        if (file_exists($configPath)) {
            $config = require $configPath;
            $default = $config['default'] ?? 'mysql';
            $driver = $config['connections'][$default]['driver'] ?? 'mysql';
        }

        $grammar = match ($driver) {
            'pgsql' => new PostgreSqlGrammarDDL(),
            'sqlite' => new SqliteGrammarDDL(),
            'sqlsrv' => new SqlServerGrammarDDL(),
            default => new MySqlGrammarDDL(),
        };

        // Paths to scan
        $paths = $this->input->getOption('path');
        // Convert relative paths to absolute
        $absolutePaths = array_map(function ($p) {
            return getcwd() . '/' . $p;
        }, $paths);

        $discovery = new EntityDiscovery();
        $generator = new MigrationGenerator($grammar, $discovery);

        $sql = $generator->generate($absolutePaths);

        return [
            '{{ class }}' => $className,
            '{{ sql }}' => $sql ?: '-- No entities found or no attributes detected',
        ];
    }
}
