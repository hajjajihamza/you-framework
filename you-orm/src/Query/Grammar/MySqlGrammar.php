<?php

namespace YouOrm\Query\Grammar;

/**
 * Class MySqlGrammar
 * Grammaire SQL spécifique à MySQL.
 */
class MySqlGrammar extends AbstractGrammar
{
    /**
     * Compile la clause LIMIT et OFFSET pour MySQL.
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
