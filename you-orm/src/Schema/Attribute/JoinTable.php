<?php

namespace YouOrm\Schema\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class JoinTable
{
    public function __construct(
        public string $name,
        public array $joinColumns = [],
        public array $inverseJoinColumns = []
    ) {
    }
}
