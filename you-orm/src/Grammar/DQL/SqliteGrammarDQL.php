<?php

namespace YouOrm\Grammar\DQL;

/**
 * Class SqliteGrammar
 * Grammaire SQL spécifique à SQLite.
 */
class SqliteGrammarDQL extends AbstractGrammarDQLDQL
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
