<?php

namespace Tests\Application;

class GenericService
{
    public function __construct(private string $type, private ?string $dbMap = null, ?int $dbIndex = null)
    {
    }
}