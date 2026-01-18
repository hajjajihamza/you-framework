<?php

namespace YouOrm\Grammar\DDL;

use YouOrm\Schema\Attribute\Column;
use YouOrm\Schema\ForeignKey;

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
     * @param ForeignKey[] $foreignKeys Les clés étrangères.
     * @return string
     */
    public function compileCreateTable(string $table, array $columns, array $foreignKeys = []): string;

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

    /**
     * Compile une contrainte de clé étrangère.
     *
     * @param ForeignKey $foreignKey
     * @return string
     */
    public function compileForeignKey(ForeignKey $foreignKey): string;

    /**
     * Compile l'ajout d'une clé étrangère via ALTER TABLE.
     *
     * @param string $table
     * @param ForeignKey $foreignKey
     * @return string
     */
    public function compileAddForeignKey(string $table, ForeignKey $foreignKey): string;

    /**
     * Compile la suppression d'une clé étrangère via ALTER TABLE.
     *
     * @param string $table
     * @param string $foreignKeyName
     * @return string
     */
    public function compileDropForeignKey(string $table, string $foreignKeyName): string;
}
