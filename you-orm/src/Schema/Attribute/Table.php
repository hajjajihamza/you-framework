<?php

namespace YouOrm\Schema\Attribute;

use Attribute;
use YouOrm\Schema\ForeignKey;

/**
 * Attribut permettant de définir la table de base de données associée à une entité.
 *
 * Cet attribut est utilisé pour mapper une classe PHP à une table spécifique
 * dans la base de données. Il permet également de configurer des options
 * supplémentaires comme le schéma ou la classe de dépôt (repository).
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    /** @var Column[] */
    private array $columns = [];

    /** @var Index[] */
    private array $indexes = [];

    /** @var ForeignKey[] */
    private array $foreignKeys = [];

    /**
     * Constructeur de l'attribut Table.
     *
     * @param string $name Le nom de la table dans la base de données.
     */
    public function __construct(
        private string $name,
    ) {
    }

    /**
     * Récupère le nom de la table.
     *
     * @return string Le nom de la table.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Définit le nom de la table.
     *
     * @param string $name Le nom de la table.
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Récupère les colonnes de la table.
     *
     * @param Column[] $columns Les colonnes de la table.
     * @return self
     */
    public function setColumns(array $columns): self
    {
        foreach ($columns as $column) {
            $this->addColumn($column);
        }
        return $this;
    }

    private function addColumn(Column $column): void
    {
        $this->columns[$column->getName()] = $column;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getColumn(string $name): ?Column
    {
        return $this->columns[$name] ?? null;
    }

    public function hasColumn(string $name): bool
    {
        return isset($this->columns[$name]);
    }

    /**
     * @param Index[] $indexes
     * @return $this
     */
    public function setIndexes(array $indexes): self
    {
        foreach ($indexes as $index) {
            $this->addIndex($index);
        }
        return $this;
    }

    private function addIndex(Index $index): void
    {
        $this->indexes[$index->getName()] = $index;
    }

    /**
     * @return Index[]
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function addForeignKey(ForeignKey $foreignKey): void
    {
        $this->foreignKeys[] = $foreignKey;
    }

    /**
     * @return ForeignKey[]
     */
    public function getForeignKeys(): array
    {
        return $this->foreignKeys;
    }
}
