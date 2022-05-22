<?php

namespace DotDi\DependencyInjection;

use DotDi\Interfaces\IDisposable;
use Exception;

interface IServiceProvider extends IDisposable
{
    /**
     * 
     * @param string $typeOrInterface 
     * @return void 
     */
    function addTransient(string $typeOrInterface, string $type = null);

    /**
     * 
     * @param string $typeOrInterface 
     * @return void 
     */
    public function addScoped(string $typeOrInterface, string $type = null);

    /**
     * 
     * @param string $typeOrInterface 
     * @return void 
     */
    function addSingleton(string $typeOrInterface, string $type = null);

    function createScope(): Container;

    function set(string $type, $instance);

    function setSingleton(string $type, $instance);
    
    /**
     * 
     * @param class-string $type 
     * @param null|array $params 
     * @return {$type}|null 
     * @throws Exception 
     */
    public function get(string $type, ?array $params = null);
}
