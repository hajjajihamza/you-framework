<?php

namespace YouOrm\Grammar\DDL;

use YouOrm\Schema\Attribute\Column;

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
     * Compile une instruction ALTER TABLE pour ajouter une colonne.
     *
     * @param string $table
     * @param Column $column
     * @return string
     */
    public function compileAddColumn(string $table, Column $column): string;

    /**
     * Compile une instruction ALTER TABLE pour supprimer une colonne.
     *
     * @param string $table
     * @param string $columnName
     * @return string
     */
    public function compileDropColumn(string $table, string $columnName): string;

    /**
     * Compile une instruction ALTER TABLE pour modifier une colonne.
     *
     * @param string $table
     * @param Column $oldColumn
     * @param Column $newColumn
     * @return string
     */
    public function compileModifyColumn(string $table, Column $oldColumn, Column $newColumn): string;

    /**
     * Enveloppe un identifiant (table, colonne) avec les caractères appropriés.
     *
     * @param string $value
     * @return string
     */
    public function wrap(string $value): string;
}
