<?php

namespace YouMake\Command\Generator;

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
            ->setDescription('Génère une nouvelle migration')
            ->addArgument('name', true, 'Le nom de la migration (ex: create_users_table)');
    }

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../Resources/stubs/migration.stub';
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getDestinationPath(string $name): string
    {
        $timestamp = date('Y_m_d_His');
        return getcwd() . '/database/migrations/' . $timestamp . '_' . $name . '.php';
    }

    /**
     * @param string $name
     * @return array<string, string>
     */
    protected function getReplacements(string $name): array
    {
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

        return [
            '{{ class }}' => $className,
            '{{ table }}' => $this->getTableNameFromMigration($name),
        ];
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getTableNameFromMigration(string $name): string
    {
        if (preg_match('/^create_(.*)_table$/', $name, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^add_.*_to_(.*)_table$/', $name, $matches)) {
            return $matches[1];
        }

        return 'table_name';
    }
}
