<?php

namespace YouOrm\Grammar\DDL;

use YouOrm\Attribute\Column;
use YouOrm\Type\ColumnType;

/**
 * Class MySqlGrammarDDL
 * Grammaire DDL pour MySQL.
 */
class MySqlGrammarDDL extends AbstractGrammarDDL
{
    protected string $wrapper = '`';

    /**
     * {@inheritDoc}
     */
    protected function getType(Column $column): string
    {
        return match ($column->getType()) {
            ColumnType::SMALL_FLOAT => 'FLOAT',
            ColumnType::BOOLEAN => 'TINYINT(1)',
            ColumnType::ARRAY => 'LONGTEXT',
            default => parent::getType($column),
        };
    }

    /**
     * {@inheritDoc}
     */
    protected function getAutoIncrementSql(): string
    {
        return 'AUTO_INCREMENT';
    }
}
