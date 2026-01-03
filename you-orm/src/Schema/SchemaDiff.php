<?php

namespace YouOrm\Schema;

use YouOrm\Schema\Attribute\Column;
use YouOrm\Schema\Attribute\Table;

/**
 * Class SchemaDiff
 * Represents the differences between two schemas.
 */
class SchemaDiff
{
    /** @var Table[] */
    public array $newTables = [];

    /** @var Table[] */
    public array $removedTables = [];

    /** @var TableDiff[] */
    public array $changedTables = [];

    public function hasChanges(): bool
    {
        return !empty($this->newTables) || !empty($this->removedTables) || !empty($this->changedTables);
    }
}

/**
 * Class TableDiff
 * Represents changes within a single table.
 */
class TableDiff
{
    /** @var Column[] */
    public array $addedColumns = [];

    /** @var Column[] */
    public array $removedColumns = [];

    /** @var ColumnDiff[] */
    public array $changedColumns = [];

    public function __construct(
        public string $tableName
    ) {
    }

    public function hasChanges(): bool
    {
        return !empty($this->addedColumns) || !empty($this->removedColumns) || !empty($this->changedColumns);
    }
}

/**
 * Class ColumnDiff
 * Represents changes to a single column.
 */
class ColumnDiff
{
    public function __construct(
        public Column $oldColumn,
        public Column $newColumn
    ) {
    }
}
