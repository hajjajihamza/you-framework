<?php

namespace YouOrm\Grammar\DDL;

use YouOrm\Schema\Attribute\Column;
use YouOrm\Schema\ForeignKey;
use YouOrm\Schema\Type\ColumnType;

/**
 * Class AbstractGrammarDDL
 * Logique de base pour la génération de DDL.
 */
abstract class AbstractGrammarDDL implements GrammarDDLInterface
{
    /**
     * @var string Le caractère d'enveloppement des identifiants (ex: ` pour MySQL, " pour PostgreSQL).
     */
    protected string $wrapper = '';

    /**
     * {@inheritDoc}
     */
    public function compileCreateTable(string $table, array $columns, array $foreignKeys = []): string
    {
        $definitions = [];

        foreach ($columns as $column) {
            $definitions[] = $this->compileColumn($column);
        }

        foreach ($foreignKeys as $foreignKey) {
            $definitions[] = $this->compileForeignKey($foreignKey);
        }

        return sprintf(
            'CREATE TABLE %s (%s)',
            $this->wrap($table),
            implode(', ', $definitions)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function compileDropTable(string $table): string
    {
        return sprintf('DROP TABLE %s', $this->wrap($table));
    }

    /**
     * {@inheritDoc}
     */
    public function compileAddColumn(string $table, Column $column): string
    {
        return sprintf(
            'ALTER TABLE %s ADD %s',
            $this->wrap($table),
            $this->compileColumn($column)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function compileDropColumn(string $table, string $columnName): string
    {
        return sprintf(
            'ALTER TABLE %s DROP COLUMN %s',
            $this->wrap($table),
            $this->wrap($columnName)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function compileModifyColumn(string $table, Column $oldColumn, Column $newColumn): string
    {
        return sprintf(
            'ALTER TABLE %s MODIFY %s',
            $this->wrap($table),
            $this->compileColumn($newColumn)
        );
    }

    /**
     * Compile la définition d'une colonne.
     *
     * @param Column $column
     * @return string
     */
    protected function compileColumn(Column $column): string
    {
        $sql = sprintf('%s %s', $this->wrap($column->getName()), $this->getType($column));

        if (!$column->isNullable()) {
            $sql .= ' NOT NULL';
        }

        if ($column->getDefault()) {
            $sql .= ' DEFAULT ' . $this->formatDefault($column->getDefault());
        }

        if ($column->isUnique()) {
            $sql .= ' UNIQUE';
        }

        if ($column->isPrimaryKey()) {
            $sql .= ' PRIMARY KEY';
        }

        if ($column->isAutoIncrement()) {
            $sql .= ' ' . $this->getAutoIncrementSql();
        }

        return $sql;
    }

    /**
     * Récupère le type SQL complet pour une colonne.
     *
     * @param Column $column
     * @return string
     */
    protected function getType(Column $column): string
    {
        $length = $column->getLength();
        $precision = $column->getPrecision();
        $scale = $column->getScale();

        return match ($column->getType()) {
            ColumnType::SMALLINT => 'SMALLINT',
            ColumnType::INTEGER => 'INT',
            ColumnType::BIGINT => 'BIGINT',
            ColumnType::DECIMAL => sprintf('NUMERIC(%d, %d)', $precision ?? 10, $scale ?? 0),
            ColumnType::SMALL_FLOAT => 'REAL',
            ColumnType::FLOAT => 'DOUBLE',
            ColumnType::STRING => sprintf('VARCHAR(%d)', $length ?? 255),
            ColumnType::TEXT => 'TEXT',
            ColumnType::UUID => 'CHAR(36)',
            ColumnType::BINARY => sprintf('VARBINARY(%d)', $length ?? 255),
            ColumnType::BLOB => 'BLOB',
            ColumnType::BOOLEAN => 'BOOLEAN',
            ColumnType::DATE => 'DATE',
            ColumnType::DATETIME => 'DATETIME',
            ColumnType::DATETIME_TZ => 'TIMESTAMP',
            ColumnType::TIME => 'TIME',
            ColumnType::ARRAY => 'LONGTEXT',
            ColumnType::JSON => 'JSON',
            default => 'VARCHAR(255)',
        };
    }

    /**
     * Récupère la syntaxe SQL pour l'auto-incrément.
     *
     * @return string
     */
    abstract protected function getAutoIncrementSql(): string;

    /**
     * Formate la valeur par défaut pour le SQL.
     *
     * @param mixed $default
     * @return string
     */
    protected function formatDefault(mixed $default): string
    {
        if (is_string($default)) {
            return sprintf("'%s'", addslashes($default));
        }

        if (is_bool($default)) {
            return $default ? '1' : '0';
        }

        if (is_null($default)) {
            return 'NULL';
        }

        return (string) $default;
    }

    /**
     * {@inheritDoc}
     */
    public function wrap(string $value): string
    {
        if ($value === '*') {
            return $value;
        }

        return sprintf('%s%s%s', $this->wrapper, $value, $this->wrapper);
    }

    /**
     * {@inheritDoc}
     */
    public function compileForeignKey(ForeignKey $foreignKey): string
    {
        $sql = sprintf(
            'CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s)',
            $this->wrap($foreignKey->name),
            $this->wrap($foreignKey->localColumn),
            $this->wrap($foreignKey->foreignTable),
            $this->wrap($foreignKey->foreignColumn)
        );

        if ($foreignKey->onDelete) {
            $sql .= ' ON DELETE ' . strtoupper($foreignKey->onDelete);
        }

        return $sql;
    }

    /**
     * {@inheritDoc}
     */
    public function compileAddForeignKey(string $table, ForeignKey $foreignKey): string
    {
        return sprintf(
            'ALTER TABLE %s ADD %s',
            $this->wrap($table),
            $this->compileForeignKey($foreignKey)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function compileDropForeignKey(string $table, string $foreignKeyName): string
    {
        return sprintf(
            'ALTER TABLE %s DROP FOREIGN KEY %s',
            $this->wrap($table),
            $this->wrap($foreignKeyName)
        );
    }
}
