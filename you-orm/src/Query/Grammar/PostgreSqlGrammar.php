<?php

namespace YouOrm\Query\Grammar;

/**
 * Class PostgreSqlGrammar
 * Grammaire SQL spécifique à PostgreSQL.
 */
class PostgreSqlGrammar extends AbstractGrammar
{
    /**
     * Compile la clause LIMIT et OFFSET pour PostgreSQL.
     */
    protected function compileLimit(?int $limit, ?int $offset): string
    {
        if ($limit === null) {
            return "";
        }

        $sql = "LIMIT " . $limit;

        if ($offset !== null) {
            $sql .= " OFFSET " . $offset;
        }

        return $sql;
    }
}
