<?php

namespace YouOrm\Grammar\DQL;

/**
 * Class MySqlGrammar
 * Grammaire SQL spécifique à MySQL.
 */
class MySqlGrammarDQL extends AbstractGrammarDQLDQL
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
