<?php

namespace YouOrm\Schema\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class OneToMany
{
    public function __construct(
        public string $targetEntity,
        public string $mappedBy
    ) {
    }
}
