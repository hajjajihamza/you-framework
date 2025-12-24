<?php

namespace YouOrm\Query\Grammar;

/**
 * Class AbstractGrammar
 * Logique de base partagée entre les différentes grammaires SQL.
 */
abstract class AbstractGrammar implements GrammarInterface
{
    /**
     * {@inheritDoc}
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
    ): string {
        $components = [
            'select' => $this->compileColumns($selects),
            'from' => $this->compileFrom($from),
            'joins' => $this->compileJoins($joins),
            'wheres' => $this->compileWheres($wheres),
            'groups' => $this->compileGroups($groups),
            'havings' => $this->compileHavings($havings),
            'orders' => $this->compileOrders($orderBys),
            'limit' => $this->compileLimit($limit, $offset),
        ];

        return $this->concatenate($components);
    }

    protected function compileColumns(array $columns): string
    {
        return "SELECT " . implode(', ', $columns);
    }

    protected function compileFrom(string $table): string
    {
        return $table ? "FROM " . $table : "";
    }

    protected function compileJoins(array $joins): string
    {
        return !empty($joins) ? implode(' ', $joins) : "";
    }

    protected function compileWheres(array $wheres): string
    {
        if (empty($wheres)) {
            return "";
        }

        $sql = "WHERE ";
        foreach ($wheres as $index => $where) {
            if ($index > 0) {
                $sql .= " " . $where['type'] . " ";
            }
            $sql .= $where['condition'];
        }

        return $sql;
    }

    protected function compileGroups(array $groups): string
    {
        return !empty($groups) ? "GROUP BY " . implode(', ', $groups) : "";
    }

    protected function compileHavings(array $havings): string
    {
        if (empty($havings)) {
            return "";
        }

        $sql = "HAVING ";
        foreach ($havings as $index => $having) {
            if ($index > 0) {
                $sql .= " " . $having['type'] . " ";
            }
            $sql .= $having['condition'];
        }

        return $sql;
    }

    protected function compileOrders(array $orders): string
    {
        return !empty($orders) ? "ORDER BY " . implode(', ', $orders) : "";
    }

    /**
     * Chaque SGBD peut avoir sa propre façon de gérer LIMIT et OFFSET.
     */
    abstract protected function compileLimit(?int $limit, ?int $offset): string;

    protected function concatenate(array $components): string
    {
        return implode(' ', array_filter($components, static fn($val) => $val !== ""));
    }
}
