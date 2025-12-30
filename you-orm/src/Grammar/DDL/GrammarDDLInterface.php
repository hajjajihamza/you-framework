<?php

namespace YouOrm\Grammar\DDL;

use YouOrm\Attribute\Column;

/**
 * Interface GrammarDDLInterface
 * Définit le contrat pour les grammaires SQL DDL spécifiques aux SGBD.
 */
interface GrammarDDLInterface
{
    /**
     * Compile une instruction CREATE TABLE.
     *
     * @param string $table Le nom de la table.
     * @param Column[] $columns Les colonnes (tableau associatif de configurations).
     * @return string
     */
    public function compileCreateTable(string $table, array $columns): string;

    /**
     * Compile une instruction DROP TABLE.
     *
     * @param string $table Le nom de la table.
     * @return string
     */
    public function compileDropTable(string $table): string;

    /**
     * Enveloppe un identifiant (table, colonne) avec les caractères appropriés.
     *
     * @param string $value
     * @return string
     */
    public function wrap(string $value): string;
}
