<?php

namespace YouOrm\Attribute;

use Attribute;
use InvalidArgumentException;
use ReflectionClass;
use YouOrm\Type\ColumnType;

/**
 * Attribut permettant de définir une colonne de base de données associée à une propriété d'entité.
 *
 * Cet attribut configure le mappage entre une propriété de classe PHP et une colonne
 * dans la table de base de données correspondante.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    /**
     * Constructeur de l'attribut Column.
     *
     * @param string $name Le nom de la colonne dans la base de données.
     * @param string $type Le type SQL de la colonne (ex: ColumnType::STRING, ColumnType::BIGINT, etc.).
     * @param int|null $length La longueur maximale de la colonne (ex: pour VARCHAR, VARBINARY) (par défaut null).
     * @param bool $nullable Indique si la colonne accepte les valeurs NULL (par défaut false).
     * @param bool $unique Indique si la colonne doit avoir une contrainte d'unicité (par défaut false).
     * @param mixed $default La valeur par défaut de la colonne (par défaut null).
     * @param array|null $enumOptions Les options d'enum (ex: ['option1', 'option2']) (par défaut null).
     * @param int|null $precision La précision pour les types décimaux ou temporels (nombre total de chiffres ou fraction de seconde) (par défaut null).
     * @param int|null $scale L'échelle pour les types décimaux (nombre de chiffres après la virgule) (par défaut null).
     * @param bool $isPrimaryKey Indique si la colonne est une clé primaire (par défaut false).
     * @param bool $isAutoIncrement Indique si la colonne est auto-incrémentée (par défaut true).
     */
    public function __construct(
        private string $name,
        private string $type = ColumnType::STRING,
        private ?int $length = null,
        private bool $nullable = false,
        private bool $unique = false,
        private mixed $default = null,
        private ?array $enumOptions = null,
        private ?int $precision = null,
        private ?int $scale = null,
        private bool $isPrimaryKey = false,
        private bool $isAutoIncrement = true
    ) {
        $this->validateType($this->type);
    }

    /**
     * Récupère le nom de la colonne.
     *
     * @return string Le nom de la colonne.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Définit le nom de la colonne.
     *
     * @param string $name Le nom de la colonne.
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Récupère le type de la colonne.
     *
     * @return string Le type de la colonne.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Définit le type de la colonne.
     *
     * @param string $type Le type de la colonne (ex: 'integer', 'string').
     * @return self
     */
    public function setType(string $type): self
    {
        $this->validateType($type);
        $this->type = $type;
        return $this;
    }

    /**
     * Récupère la longueur de la colonne.
     *
     * @return int|null La longueur ou null si non définie.
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * Définit la longueur de la colonne.
     *
     * @param int|null $length La longueur.
     * @return self
     */
    public function setLength(?int $length): self
    {
        $this->length = $length;
        return $this;
    }

    /**
     * Vérifie si la colonne accepte les valeurs NULL.
     *
     * @return bool Vrai si NULL est accepté, faux sinon.
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * Définit si la colonne accepte les valeurs NULL.
     *
     * @param bool $nullable Vrai pour accepter NULL.
     * @return self
     */
    public function setNullable(bool $nullable): self
    {
        $this->nullable = $nullable;
        return $this;
    }

    /**
     * Vérifie si la colonne doit être unique.
     *
     * @return bool Vrai si unique, faux sinon.
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

    /**
     * Définit si la colonne doit être unique.
     *
     * @param bool $unique Vrai pour unique.
     * @return self
     */
    public function setUnique(bool $unique): self
    {
        $this->unique = $unique;
        return $this;
    }

    /**
     * Récupère la valeur par défaut.
     *
     * @return mixed La valeur par défaut.
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * Définit la valeur par défaut.
     *
     * @param mixed $default La valeur par défaut.
     * @return self
     */
    public function setDefault(mixed $default): self
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Récupère le type d'enum associé.
     *
     * @return array|null Le type d'enum associé ou null si non défini.
     */
    public function getEnumOptions(): ?array
    {
        return $this->enumOptions;
    }

    /**
     * Définit le type d'enum associé.
     *
     * @param array|null $enumOptions Le type d'enum associé.
     * @return self
     */
    public function setEnumOptions(?array $enumOptions): self
    {
        $this->enumOptions = $enumOptions;
        return $this;
    }

    /**
     * Récupère la précision (pour les décimaux).
     *
     * @return int|null La précision ou null si non définie.
     */
    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    /**
     * Définit la précision (pour les décimaux).
     *
     * @param int|null $precision La précision.
     * @return self
     */
    public function setPrecision(?int $precision): self
    {
        $this->precision = $precision;
        return $this;
    }

    /**
     * Récupère l'échelle (pour les décimaux).
     *
     * @return int|null L'échelle ou null si non définie.
     */
    public function getScale(): ?int
    {
        return $this->scale;
    }

    /**
     * Définit l'échelle (pour les décimaux).
     *
     * @param int|null $scale L'échelle.
     * @return self
     */
    public function setScale(?int $scale): self
    {
        $this->scale = $scale;
        return $this;
    }

    /**
     * Indique si la colonne est une clé primaire.
     * @return bool
     */
    public function isPrimaryKey(): bool
    {
        return $this->isPrimaryKey;
    }

    /**
     * Indique si la colonne est une clé primaire.
     * @param bool $isPrimaryKey
     * @return $this
     */
    public function setPrimary(bool $isPrimaryKey): self {
        $this->isPrimaryKey = $isPrimaryKey;
        return $this;
    }

    /**
     * Indique si la colonne est auto-incrémentée.
     * @return bool
     */
    public function isAutoIncrement(): bool
    {
        return $this->isAutoIncrement;
    }

    /**
     * Indique si la colonne est auto-incrémentée.
     * @param bool $isAutoIncrement
     * @return $this
     */
    public function setAutoIncrement(bool $isAutoIncrement): self {
        $this->isAutoIncrement = $isAutoIncrement;
        return $this;
    }

    /**
     * Valide si le type de colonne est supporté.
     *
     * @param string $type Le type à valider.
     * @throws InvalidArgumentException Si le type n'est pas valide.
     */
    private function validateType(string $type): void
    {
        $reflection = new ReflectionClass(ColumnType::class);
        $validTypes = $reflection->getConstants();

        if (!in_array($type, $validTypes, true)) {
            throw new InvalidArgumentException(
                sprintf('Le type "%s" n\'est pas un type de colonne valide. Les types supportés sont : %s', $type, implode(', ', $validTypes))
            );
        }
    }
}
