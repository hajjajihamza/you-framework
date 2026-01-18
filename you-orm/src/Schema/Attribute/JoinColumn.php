<?php

namespace YouOrm\Schema\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class JoinColumn
{
    public function __construct(
        public string $name,
        public string $referencedColumnName = 'id',
        public bool $nullable = true,
        public ?string $onDelete = null
    ) {
    }
}
