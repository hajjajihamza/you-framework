<?php

namespace YouOrm\Grammar\DDL;

use YouOrm\Attribute\Column;
use YouOrm\Type\ColumnType;

/**
 * Class PostgreSqlGrammarDDL
 * Grammaire DDL pour PostgreSQL.
 */
class PostgreSqlGrammarDDL extends AbstractGrammarDDL
{
    protected string $wrapper = '"';

    /**
     * {@inheritDoc}
     */
    protected function getType(Column $column): string
    {
        if ($column->isAutoIncrement()) {
            return match ($column->getType()) {
                ColumnType::BIGINT => 'BIGSERIAL',
                default => 'SERIAL',
            };
        }

        return match ($column->getType()) {
            ColumnType::BINARY,  ColumnType::BLOB => 'BYTEA',
            ColumnType::DATETIME_TZ => 'TIMESTAMP(0) WITHOUT TIME ZONE',
            ColumnType::TIME => 'TIME(0) WITHOUT TIME ZONE',
            ColumnType::ARRAY => 'CLOB',
            default => parent::getType($column),
        };
    }

    /**
     * {@inheritDoc}
     */
    protected function getAutoIncrementSql(): string
    {
        // Pour PostgreSQL, SERIAL/BIGSERIAL sont des types qui gèrent l'auto-incrément.
        // On ne rajoute rien à la fin de la ligne.
        return '';
    }
}
