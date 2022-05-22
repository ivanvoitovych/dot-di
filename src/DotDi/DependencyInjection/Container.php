<?php

namespace DotDi\DependencyInjection;

use DotDi\Interfaces\IDisposable;

class Container implements IDisposable
{
    public function __construct(public IServiceProvider $serviceProvider)
    {
    }

    public function dispose()
    {
        $this->serviceProvider->dispose();
        unset($this->serviceProvider);
    }
}
