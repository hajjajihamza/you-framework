<?php

namespace YouOrm\Query\Grammar;

/**
 * Class SqlServerGrammar
 * Grammaire SQL spécifique à SQL Server.
 */
class SqlServerGrammar extends AbstractGrammar
{
    /**
     * Compile la clause LIMIT et OFFSET pour SQL Server.
     * Note: Utilise OFFSET ... FETCH (SQL Server 2012+).
     */
    protected function compileLimit(?int $limit, ?int $offset): string
    {
        if ($limit === null && $offset === null) {
            return "";
        }

        // SQL Server requiert un ORDER BY pour utiliser OFFSET FETCH.
        // On suppose que l'utilisateur l'a défini ou on pourrait lever une exception.

        $offsetValue = $offset ?? 0;
        $sql = "OFFSET " . $offsetValue . " ROWS";

        if ($limit !== null) {
            $sql .= " FETCH NEXT " . $limit . " ROWS ONLY";
        }

        return $sql;
    }
}
