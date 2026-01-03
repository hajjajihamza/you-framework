<?php

namespace YouOrm\Grammar\DDL;

use YouOrm\Schema\Attribute\Column;
use YouOrm\Schema\Type\ColumnType;

/**
 * Class SqlServerGrammarDDL
 * Grammaire DDL pour SQL Server.
 */
class SqlServerGrammarDDL extends AbstractGrammarDDL
{
    /**
     * {@inheritDoc}
     */
    public function wrap(string $value): string
    {
        if ($value === '*') {
            return $value;
        }

        return sprintf('[%s]', $value);
    }

    /**
     * {@inheritDoc}
     */
    protected function getType(Column $column): string
    {
        $length = $column->getLength();

        return match ($column->getType()) {
            ColumnType::STRING => sprintf('NVARCHAR(%d)', $length ?? 255),
            ColumnType::TEXT => 'NVARCHAR(MAX)',
            ColumnType::UUID => 'UNIQUEIDENTIFIER',
            ColumnType::BLOB => 'VARBINARY(MAX)',
            ColumnType::BOOLEAN => 'BIT',
            ColumnType::TIME => 'TIME(0)',
            ColumnType::ARRAY, ColumnType::JSON => 'VARCHAR(MAX)',
            default => parent::getType($column),
        };
    }

    /**
     * {@inheritDoc}
     */
    protected function getAutoIncrementSql(): string
    {
        return 'IDENTITY(1,1)';
    }
}
