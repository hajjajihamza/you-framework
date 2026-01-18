<?php

namespace YouOrm\Schema;

readonly class ForeignKey
{
    public function __construct(
        public string $name,
        public string $localColumn,
        public string $foreignTable,
        public string $foreignColumn,
        public ?string $onDelete = null
    ) {
    }
}
