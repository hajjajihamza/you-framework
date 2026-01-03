<?php

namespace YouOrm\Schema\Attribute;

use Attribute;
use InvalidArgumentException;
use ReflectionClass;
use YouOrm\Schema\Type\ColumnType;

/**
 * Attribut permettant de définir une colonne de base de données associée à une propriété d'entité.
 *
 * Cet attribut configure le mappage entre une propriété de classe PHP et une colonne
 * dans la table de base de données correspondante.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Column
{
    /**
     * Constructeur de la classe Column.
     *
     * @param string $name Le nom de la colonne
     * @param string $type Le type de la colonne
     * @param int|null $length La longueur de la colonne (optionnel)
     * @param bool $nullable Indique si la colonne peut contenir des valeurs nulles (par défaut true)
     * @param mixed $default La valeur par défaut de la colonne (optionnel)
     * @param bool $unique Indique si la colonne doit être unique (par défaut false)
     * @param bool $primaryKey Indique si la colonne est une clé primaire (par défaut false)
     * @param bool $autoIncrement Indique si la colonne est auto-incrémentée (par défaut false)
     * @param int|null $precision La précision de la colonne pour les types numériques (optionnel)
     * @param int|null $scale L'échelle de la colonne pour les types numériques (optionnel)
     * @param array $options Tableau d'options supplémentaires pour la colonne
     */
    public function __construct(
        private string $name,
        private string $type = ColumnType::STRING,
        private ?int   $length = null,
        private bool   $nullable = true,
        private mixed  $default = null,
        private bool   $unique = false,
        private bool   $primaryKey = false,
        private bool   $autoIncrement = false,
        private ?int   $precision = null,
        private ?int   $scale = null,
        private array  $options = []
    )
    {
        $this->validateType($this->type);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function isPrimaryKey(): bool
    {
        return $this->primaryKey;
    }

    public function isAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function getScale(): ?int
    {
        return $this->scale;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Compares this column with another to see if they are different in schema.
     */
    public function equals(Column $other): bool
    {
        return $this->type === $other->getType()
            && $this->length === $other->getLength()
            && $this->nullable === $other->isNullable()
            && $this->formatDefault($this->default) === $this->formatDefault($other->getDefault())
            && $this->unique === $other->isUnique()
            && $this->primaryKey === $other->isPrimaryKey()
            && $this->autoIncrement === $other->isAutoIncrement()
            && $this->precision === $other->getPrecision()
            && $this->scale === $other->getScale();
    }

    private function formatDefault(mixed $value): string
    {
        if (is_string($value)) {
            return sprintf("'%s'", addslashes($value));
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_null($value)) {
            return 'NULL';
        }

        return (string) $value;
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
