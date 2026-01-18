<?php

namespace YouOrm\Schema\Entity;

use ReflectionClass;
use YouOrm\Schema\Attribute\Column;
use YouOrm\Schema\Attribute\JoinColumn;
use YouOrm\Schema\Attribute\JoinTable;
use YouOrm\Schema\Attribute\ManyToMany;
use YouOrm\Schema\Attribute\ManyToOne;
use YouOrm\Schema\Attribute\OneToMany;
use YouOrm\Schema\Attribute\Table;
use YouOrm\Schema\ForeignKey;
use YouOrm\Schema\Schema;
use YouOrm\Schema\Type\ColumnType;

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
        $extraTables = [];

        foreach ($entityClasses as $className) {
            $table = $this->readEntity($className, $extraTables);
            if ($table) {
                $tables[$table->getName()] = $table;
            }
        }

        // Merge extra tables (like pivot tables) avoiding duplicates
        foreach ($extraTables as $extraTable) {
            if (!isset($tables[$extraTable->getName()])) {
                $tables[$extraTable->getName()] = $extraTable;
            }
        }

        return new Schema(array_values($tables));
    }

    /**
     * Read schema for a single entity class.
     *
     * @param string $className
     * @param array $extraTables
     * @return Table|null
     */
    private function readEntity(string $className, array &$extraTables = []): ?Table
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
                // Regular Column
                $columnAttrs = $property->getAttributes(Column::class);
                if (!empty($columnAttrs)) {
                    /** @var Column $column */
                    $column = $columnAttrs[0]->newInstance();
                    $columns[] = $column;
                }

                // ManyToOne Relation
                $manyToOneAttrs = $property->getAttributes(ManyToOne::class);
                if (!empty($manyToOneAttrs)) {
                    /** @var ManyToOne $manyToOne */
                    $manyToOne = $manyToOneAttrs[0]->newInstance();
                    $targetEntity = $manyToOne->targetEntity;
                    $targetTable = $this->getTableName($targetEntity);

                    $joinColumnAttrs = $property->getAttributes(JoinColumn::class);
                    /** @var ?JoinColumn $joinColumn */
                    $joinColumn = !empty($joinColumnAttrs) ? $joinColumnAttrs[0]->newInstance() : null;

                    $fkColumnName = $joinColumn?->name ?? strtolower($property->getName()) . '_id';
                    $referencedColumn = $joinColumn?->referencedColumnName ?? 'id';

                    $columns[] = new Column(
                        name: $fkColumnName,
                        type: ColumnType::INTEGER, // Default to INTEGER for FK
                        nullable: $joinColumn?->nullable ?? true
                    );

                    $table->addForeignKey(new ForeignKey(
                        name: "fk_{$table->getName()}_{$fkColumnName}",
                        localColumn: $fkColumnName,
                        foreignTable: $targetTable,
                        foreignColumn: $referencedColumn,
                        onDelete: $joinColumn?->onDelete
                    ));
                }

                // OneToMany Relation
                $oneToManyAttrs = $property->getAttributes(OneToMany::class);
                if (!empty($oneToManyAttrs)) {
                    /** @var OneToMany $oneToMany */
                    $oneToMany = $oneToManyAttrs[0]->newInstance();
                    $targetEntity = $oneToMany->targetEntity;
                    $targetTable = $this->getTableName($targetEntity);

                    $joinColumnAttrs = $property->getAttributes(JoinColumn::class);
                    /** @var ?JoinColumn $joinColumn */
                    $joinColumn = !empty($joinColumnAttrs) ? $joinColumnAttrs[0]->newInstance() : null;

                    $fkColumnName = $joinColumn?->name ?? strtolower($property->getName()) . '_id';
                    $referencedColumn = $joinColumn?->referencedColumnName ?? 'id';

                    $columns[] = new Column(
                        name: $fkColumnName,
                        type: ColumnType::INTEGER, // Default to INTEGER for FK
                        nullable: $joinColumn?->nullable ?? true
                    );

                    $table->addForeignKey(new ForeignKey(
                        name: "fk_{$table->getName()}_{$fkColumnName}",
                        localColumn: $fkColumnName,
                        foreignTable: $targetTable,
                        foreignColumn: $referencedColumn,
                        onDelete: $joinColumn?->onDelete
                    ));
                }

                // ManyToMany Relation
                $manyToManyAttrs = $property->getAttributes(ManyToMany::class);
                if (!empty($manyToManyAttrs)) {
                    /** @var ManyToMany $manyToMany */
                    $manyToMany = $manyToManyAttrs[0]->newInstance();

                    // Only process on the owning side (no mappedBy)
                    if ($manyToMany->mappedBy === null) {
                        $joinTableAttrs = $property->getAttributes(JoinTable::class);
                        /** @var JoinTable $joinTable */
                        $joinTable = !empty($joinTableAttrs) ? $joinTableAttrs[0]->newInstance() : null;

                        $targetEntity = $manyToMany->targetEntity;
                        $targetTable = $this->getTableName($targetEntity);

                        $pivotTableName = $joinTable?->name ?? $table->getName() . '_' . $targetTable;
                        $pivotTable = new Table($pivotTableName);

                        // Local FK in pivot
                        $localFkName = $joinTable?->joinColumns[0]['name'] ?? $table->getName() . '_id';
                        $localRefName = $joinTable?->joinColumns[0]['referencedColumnName'] ?? 'id';

                        // Target FK in pivot
                        $targetFkName = $joinTable?->inverseJoinColumns[0]['name'] ?? $targetTable . '_id';
                        $targetRefName = $joinTable?->inverseJoinColumns[0]['referencedColumnName'] ?? 'id';

                        $pivotColumns = [
                            new Column($localFkName, ColumnType::INTEGER, primaryKey: true),
                            new Column($targetFkName, ColumnType::INTEGER, primaryKey: true),
                        ];
                        $pivotTable->setColumns($pivotColumns);

                        $pivotTable->addForeignKey(new ForeignKey(
                            name: "fk_{$pivotTableName}_{$localFkName}",
                            localColumn: $localFkName,
                            foreignTable: $table->getName(),
                            foreignColumn: $localRefName,
                            onDelete: 'CASCADE'
                        ));

                        $pivotTable->addForeignKey(new ForeignKey(
                            name: "fk_{$pivotTableName}_{$targetFkName}",
                            localColumn: $targetFkName,
                            foreignTable: $targetTable,
                            foreignColumn: $targetRefName,
                            onDelete: 'CASCADE'
                        ));

                        $extraTables[] = $pivotTable;
                    }
                }
            }

            return $table->setColumns($columns);
        } catch (\ReflectionException $e) {
            return null;
        }
    }

    private function getTableName(string $className): string
    {
        if (!class_exists($className)) {
            throw new \RuntimeException("Class $className does not exist.");
        }
        $reflection = new ReflectionClass($className);
        $attributes = $reflection->getAttributes(Table::class);
        if (empty($attributes)) {
            throw new \RuntimeException("Class $className is missing #[Table] attribute.");
        }
        return $attributes[0]->newInstance()->getName();
    }
}
