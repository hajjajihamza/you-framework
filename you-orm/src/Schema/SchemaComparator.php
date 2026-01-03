<?php

namespace YouOrm\Schema;

use YouOrm\Schema\Attribute\Table;

/**
 * Class SchemaComparator
 * Compares two schemas and returns a SchemaDiff.
 */
class SchemaComparator
{
    /**
     * Compare two schemas.
     *
     * @param Schema $oldSchema (from Database)
     * @param Schema $newSchema (from Entities)
     * @return SchemaDiff
     */
    public function compare(Schema $oldSchema, Schema $newSchema): SchemaDiff
    {
        $diff = new SchemaDiff();

        // Check for new and changed tables
        foreach ($newSchema->getTables() as $newTable) {
            if (!$oldSchema->hasTable($newTable->getName())) {
                $diff->newTables[] = $newTable;
            } else {
                $tableDiff = $this->compareTables($oldSchema->getTable($newTable->getName()), $newTable);
                if ($tableDiff->hasChanges()) {
                    $diff->changedTables[] = $tableDiff;
                }
            }
        }

        // Check for removed tables
        foreach ($oldSchema->getTables() as $oldTable) {
            if (!$newSchema->hasTable($oldTable->getName())) {
                $diff->removedTables[] = $oldTable;
            }
        }

        return $diff;
    }

    private function compareTables(Table $oldTable, Table $newTable): TableDiff
    {
        $diff = new TableDiff($newTable->getName());

        // Check for new and changed columns
        foreach ($newTable->getColumns() as $newColumn) {
            if (!$oldTable->hasColumn($newColumn->getName())) {
                $diff->addedColumns[] = $newColumn;
            } else {
                $oldColumn = $oldTable->getColumn($newColumn->getName());
                if (!$oldColumn?->equals($newColumn)) {
                    $diff->changedColumns[] = new ColumnDiff($oldColumn, $newColumn);
                }
            }
        }

        // Check for removed columns
        foreach ($oldTable->getColumns() as $oldColumn) {
            if (!$newTable->hasColumn($oldColumn?->getName() ?? '')) {
                $diff->removedColumns[] = $oldColumn;
            }
        }

        return $diff;
    }
}
