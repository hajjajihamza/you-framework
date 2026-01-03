<?php

namespace YouOrm\Grammar\DDL;

use YouOrm\Schema\Attribute\Column;
use YouOrm\Schema\Type\ColumnType;

/**
 * Class SqliteGrammarDDL
 * Grammaire DDL pour SQLite.
 */
class SqliteGrammarDDL extends AbstractGrammarDDL
{
    protected string $wrapper = '"';

    /**
     * {@inheritDoc}
     */
    protected function getType(Column $column): string
    {
        return match ($column->getType()) {
            ColumnType::SMALLINT, ColumnType::INTEGER, ColumnType::BIGINT, ColumnType::BOOLEAN => 'INTEGER',
            ColumnType::BINARY => 'BLOB',
            ColumnType::DATETIME_TZ => 'DATETIME',
            ColumnType::ARRAY, ColumnType::JSON => 'CLOB',
            default => 'TEXT',
        };
    }

    /**
     * {@inheritDoc}
     */
    protected function getAutoIncrementSql(): string
    {
        return 'AUTOINCREMENT';
    }
}
