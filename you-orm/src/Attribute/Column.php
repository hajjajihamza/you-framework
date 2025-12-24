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
     * @param string $type Le type SQL de la colonne (par défaut 'string').
     * @param int|null $length La longueur maximale de la colonne (ex: pour VARCHAR) (par défaut null).
     * @param bool $nullable Indique si la colonne accepte les valeurs NULL (par défaut false).
     * @param bool $unique Indique si la colonne doit avoir une contrainte d'unicité (par défaut false).
     * @param mixed $default La valeur par défaut de la colonne (par défaut null).
     * @param string|null $enumType La classe d'enum (BackedEnum) associée à cette colonne (par défaut null).
     * @param int|null $precision La précision de la colonne pour les types décimaux (nombre total de chiffres) (par défaut null).
     * @param int|null $scale L'échelle de la colonne pour les types décimaux (nombre de chiffres après la virgule) (par défaut null).
     */
    public function __construct(
        private string $name,
        private string $type = ColumnType::STRING,
        private ?int $length = null,
        private bool $nullable = false,
        private bool $unique = false,
        private mixed $default = null,
        private ?string $enumType = null,
        private ?int $precision = null,
        private ?int $scale = null
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
     * @return string|null La classe de l'enum ou null si non définie.
     */
    public function getEnumType(): ?string
    {
        return $this->enumType;
    }

    /**
     * Définit le type d'enum associé.
     *
     * @param string|null $enumType La classe de l'enum.
     * @return self
     */
    public function setEnumType(?string $enumType): self
    {
        $this->enumType = $enumType;
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
