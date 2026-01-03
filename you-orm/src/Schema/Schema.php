<?php

namespace YouOrm\Schema;

use \YouOrm\Schema\Attribute\Table;

/**
 * Class Schema
 * Represents the complete database schema.
 */
class Schema
{
    /** @var Table[] */
    private array $tables = [];

    /**
     * @param Table[] $tables
     */
    public function __construct(array $tables = [])
    {
        foreach ($tables as $table) {
            $this->addTable($table);
        }
    }

    public function addTable(Table $table): void
    {
        $this->tables[$table->getName()] = $table;
    }

    /**
     * @return Table[]
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    public function getTable(string $name): ?Table
    {
        return $this->tables[$name] ?? null;
    }

    public function hasTable(string $name): bool
    {
        return isset($this->tables[$name]);
    }
}
