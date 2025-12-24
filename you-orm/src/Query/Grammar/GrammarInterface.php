<?php

namespace YouOrm\Query\Grammar;

/**
 * Interface GrammarInterface
 * Définit le contrat pour les grammaires SQL spécifiques aux SGBD.
 */
interface GrammarInterface
{
    /**
     * Compile une requête SELECT en SQL.
     *
     * @param array $selects
     * @param string $from
     * @param array $joins
     * @param array $wheres
     * @param array $groups
     * @param array $havings
     * @param array $orderBys
     * @param int|null $limit
     * @param int|null $offset
     * @return string
     */
    public function compileSelect(
        array $selects,
        string $from,
        array $joins,
        array $wheres,
        array $groups,
        array $havings,
        array $orderBys,
        ?int $limit,
        ?int $offset
    ): string;
}
