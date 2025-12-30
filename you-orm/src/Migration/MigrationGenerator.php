<?php

namespace YouOrm\Migration;

use ReflectionClass;
use YouOrm\Attribute\Column;
use YouOrm\Attribute\Table;
use YouOrm\Discovery\EntityDiscovery;
use YouOrm\Grammar\DDL\GrammarDDLInterface;

/**
 * Class MigrationGenerator
 * Responsible for generating migration SQL from discovered entities.
 */
class MigrationGenerator
{
    /**
     * @param GrammarDDLInterface $grammar
     * @param EntityDiscovery $discovery
     */
    public function __construct(
        protected GrammarDDLInterface $grammar,
        protected EntityDiscovery $discovery
    ) {
    }

    /**
     * Generate SQL for the given paths.
     *
     * @param string $directory The directory to scan for entities.
     * @return string
     */
    public function generate(string $directory): string
    {
        $entities = $this->discovery->discover($directory);
        $sqlParts = [];

        foreach ($entities as $entityClass) {
            $sqlParts[] = $this->generateSqlForEntity($entityClass);
        }

        return implode("\n\n", array_filter($sqlParts));
    }

    /**
     * Generate SQL for a single entity class.
     *
     * @param string $entityClass
     * @return string|null
     */
    protected function generateSqlForEntity(string $entityClass): ?string
    {
        try {
            $reflection = new ReflectionClass($entityClass);

            // Get Table attribute
            $tableAttributes = $reflection->getAttributes(Table::class);
            if (empty($tableAttributes)) {
                return null;
            }

            /** @var Table $tableAttr */
            $tableAttr = $tableAttributes[0]->newInstance();
            $tableName = $tableAttr->getName();

            // Get Column attributes
            $columns = [];
            foreach ($reflection->getProperties() as $property) {
                $columnAttributes = $property->getAttributes(Column::class);
                if (empty($columnAttributes)) {
                    continue;
                }

                $columns[] = $columnAttributes[0]->newInstance();
            }

            if (empty($columns)) {
                return null;
            }

            return $this->grammar->compileCreateTable($tableName, $columns) . ';';

        } catch (\ReflectionException $e) {
            // Handle error or log
            return "-- Error generating SQL for $entityClass: " . $e->getMessage();
        }
    }
}
