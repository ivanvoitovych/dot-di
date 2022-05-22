<?php

namespace Tests\Application;

class BarService
{
    public function __construct(public FooService $fooService)
    {
    }
}
