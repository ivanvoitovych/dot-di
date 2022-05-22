<?php

namespace Tests\Application;

class TaxService implements ITaxService
{
    public function __construct(
        public FooService $fooService,
        private BarService $barService,
        private ?HttpContext $httpContext
    ) {
    }
}
