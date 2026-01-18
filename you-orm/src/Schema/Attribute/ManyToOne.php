<?php

namespace YouOrm\Schema\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class ManyToOne
{
    public function __construct(
        public string $targetEntity,
        public ?string $inversedBy = null
    ) {
    }
}
