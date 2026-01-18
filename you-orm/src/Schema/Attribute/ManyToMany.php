<?php

namespace YouOrm\Schema\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class ManyToMany
{
    public function __construct(
        public string $targetEntity,
        public ?string $inversedBy = null,
        public ?string $mappedBy = null
    ) {
    }
}
