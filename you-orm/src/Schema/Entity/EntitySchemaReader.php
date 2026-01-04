<?php

namespace YouOrm\Schema\Entity;

use ReflectionClass;
use YouOrm\Schema\Attribute\Column;
use YouOrm\Schema\Attribute\Table;
use YouOrm\Schema\Schema;

/**
 * Class EntitySchemaReader
 * Build a Schema object from discovered entities using reflection.
 */
readonly class EntitySchemaReader
{
    /**
     * Read schema from entities in the given directory.
     *
     * @param string $directory
     * @return Schema
     */
    public function read(string $directory): Schema
    {
        $entityClasses = discover_classes($directory);
        $tables = [];

        foreach ($entityClasses as $className) {
            $table = $this->readEntity($className);
            if ($table) {
                $tables[] = $table;
            }
        }

        return new Schema($tables);
    }

    /**
     * Read schema for a single entity class.
     *
     * @param string $className
     * @return Table|null
     */
    private function readEntity(string $className): ?Table
    {
        try {
            $reflection = new ReflectionClass($className);

            $tableAttrs = $reflection->getAttributes(Table::class);
            if (empty($tableAttrs)) {
                return null;
            }

            /** @var Table $table */
            $table = $tableAttrs[0]->newInstance();

            $columns = [];
            foreach ($reflection->getProperties() as $property) {
                $columnAttrs = $property->getAttributes(Column::class);
                if (empty($columnAttrs)) {
                    continue;
                }

                /** @var Column $column */
                $column = $columnAttrs[0]->newInstance();

                $columns[] = $column;
            }

            // TODO: Index discovery could be added here if there's an Index attribute

            return $table->setColumns($columns);
        } catch (\ReflectionException $e) {
            return null;
        }
    }
}
