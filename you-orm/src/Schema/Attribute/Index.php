<?php

namespace YouOrm\Schema\Attribute;

/**
 * Class Index
 * Represents an index in a database table.
 */
class Index
{
    public function __construct(
        private string $name,
        private array $columns,
        private bool $unique = false,
        private bool $primary = false
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function getColumns(): array
    {
        return $this->columns;
    }
    public function isUnique(): bool
    {
        return $this->unique;
    }
    public function isPrimary(): bool
    {
        return $this->primary;
    }
}
