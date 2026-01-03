<?php

namespace YouOrm\Grammar\DDL;

use YouOrm\Schema\Attribute\Column;
use YouOrm\Schema\Type\ColumnType;

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
            ColumnType::DECIMAL => sprintf('DECIMAL(%d, %d)', $column->getPrecision() ?? 10, $column->getScale() ?? 0),
            ColumnType::SMALL_FLOAT => 'FLOAT',
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
