<?php

namespace YouOrm\Query\Grammar;

/**
 * Class SqliteGrammar
 * Grammaire SQL spécifique à SQLite.
 */
class SqliteGrammar extends AbstractGrammar
{
    /**
     * Compile la clause LIMIT et OFFSET pour SQLite.
     * SQLite utilise la même syntaxe que MySQL/PostgreSQL pour LIMIT/OFFSET.
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
