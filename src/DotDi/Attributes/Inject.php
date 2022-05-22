<?php

namespace DotDi\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Inject
{
    public function __construct(public ?array $params = null)
    {
    }
}
