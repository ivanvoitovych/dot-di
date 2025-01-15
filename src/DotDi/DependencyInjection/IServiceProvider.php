<?php

namespace DotDi\DependencyInjection;

use DotDi\Interfaces\IDisposable;
use Exception;

interface IServiceProvider extends IDisposable
{
    /**
     * 
     * @param string $typeOrInterface
     * @param callable|string|NULL $type 
     * @return void 
     */
    function addTransient(string $typeOrInterface, $type = null);

    /**
     * 
     * @param string $typeOrInterface
     * @param callable|string|NULL $type
     * @return void 
     */
    public function addScoped(string $typeOrInterface, $type = null);

    /**
     * 
     * @param string $typeOrInterface 
     * @param callable|string|NULL $type
     * @return void 
     */
    function addSingleton(string $typeOrInterface, $type = null);

    /**
     * 
     * @param string $typeOrInterface 
     * @param string $lifetimeScope 
     * @param callable $factory 
     * @return mixed 
     */
    function addFactory(string $typeOrInterface, string $lifetimeScope, callable $factory);

    function createScope(): Container;

    function set(string $type, $instance);

    function setSingleton(string $type, $instance);

    /**
     *
     * @template T
     * @param class-string<T> $type
     * @param null|array $params
     * @return T|null
     * @throws Exception
     */
    public function get(string $type, ?array $params = null): ?object;

    /**
     *
     * @template T
     * @param class-string<T> $type
     * @param null|array $params
     * @return T[]
     * @throws Exception
     */
    public function getAll(string $type, ?array $params = null): array;
}
