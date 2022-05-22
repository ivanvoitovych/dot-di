<?php

namespace DotDi\DependencyInjection;

use DotDi\Interfaces\IDisposable;
use Exception;

class ServiceProvider implements IServiceProvider, IDisposable
{
    const LIFETIME_TRANSIENT = 'TRANSIENT';
    const LIFETIME_SCOPED = 'SCOPED';
    const LIFETIME_SINGLETON = 'SINGLETON';
    // Transient
    // Scoped
    // Singleton
    // services.CreateScope();
    // provider = serviceScope.ServiceProvider;
    // provider.GetRequiredService<OperationLogger>();
    // IDisposable: Dispose();
    /**
     * 
     * @var array<string, array<string, string>[]>
     */
    private array $_registry = [];
    private array $_instances = [];
    private Scope $_singletonScope;
    /**
     * 
     * @var IDisposable[]
     */
    private array $toDispose = [];
    private bool $disposed = false;
    public function __construct()
    {
        $this->_singletonScope = new Scope();
    }

    public function dispose()
    {
        if ($this->disposed) {
            return;
        }
        $this->disposed = true;
        foreach ($this->toDispose as $disposable) {
            // echo "Disposing transient " . get_class($disposable) . " \n";
            $disposable->dispose();
        }
        foreach ($this->_instances as $instance) {
            if ($instance instanceof IDisposable) {
                // echo "Disposing scoped " . get_class($instance) . " \n";
                $instance->dispose();
            }
        }
        unset($this->toDispose);
        unset($this->_instances);
    }

    /**
     * 
     * @param string $typeOrInterface 
     * @return void 
     */
    public function addTransient(string $typeOrInterface, string $type = null)
    {
        $this->_registry[$typeOrInterface][] = [$type ?? $typeOrInterface, self::LIFETIME_TRANSIENT];
    }

    /**
     * 
     * @param string $typeOrInterface 
     * @return void 
     */
    public function addScoped(string $typeOrInterface, string $type = null)
    {
        $this->_registry[$typeOrInterface][] = [$type ?? $typeOrInterface, self::LIFETIME_SCOPED];
    }

    /**
     * 
     * @param string $typeOrInterface 
     * @return void 
     */
    public function addSingleton(string $typeOrInterface, string $type = null)
    {
        $this->_registry[$typeOrInterface][] = [$type ?? $typeOrInterface, self::LIFETIME_SINGLETON];
    }

    public function createScope(): Container
    {
        $container = new Container(clone $this);
        $container->serviceProvider->set(Container::class, $container);
        return $container;
    }

    public function set(string $type, $instance)
    {
        $targetType = get_class($instance);
        $this->addScoped($type, $targetType);
        $this->_instances[$targetType] = $instance;
    }

    public function setSingleton(string $type, $instance)
    {
        $targetType = get_class($instance);
        $this->addSingleton($type, $targetType);
        $this->_singletonScope->_instances[$targetType] = $instance;
    }

    /**
     * 
     * @param class-string $type 
     * @param null|array $params 
     * @return {$type}|null 
     * @throws Exception 
     */
    public function get(string $type, ?array $params = null)
    {
        // echo "Resolving $type $params \n";
        if (isset($this->_registry[$type])) {
            $count = count($this->_registry[$type]);
            $instanceTypeTuple = $this->_registry[$type][$count - 1]; // last one registered
            $instanceType = $instanceTypeTuple[0];
            $lifetimeScope = $instanceTypeTuple[1];
            if ($lifetimeScope === self::LIFETIME_TRANSIENT) {
                $transientInstance = $this->resolve($instanceType, $params);
                if ($transientInstance instanceof IDisposable) {
                    $this->toDispose[] = $transientInstance;
                }
                return $transientInstance;
            }
            $instanceKey = $instanceType . ($params ? json_encode($params) : '');
            if ($lifetimeScope === self::LIFETIME_SINGLETON) {
                // use singleton container
                if (!isset($this->_singletonScope->_instances[$instanceKey])) {
                    $this->_singletonScope->_instances[$instanceKey]
                        = $this->resolve($instanceType, $params);
                }
                return $this->_singletonScope->_instances[$instanceKey];
            }
            // echo "Searching for $instanceKey \n";
            if (!isset($this->_instances[$instanceKey])) {
                $this->_instances[$instanceKey] = $this->resolve($instanceType, $params);
            }
            return $this->_instances[$instanceKey];
        }
        return null;
    }

    private function resolve(string $type, ?array $params = null)
    {
        $dependencies = ServiceProviderHelper::getDependencies($type, $this, $params);
        if (!empty($dependencies)) {
            return new $type(...$dependencies);
        }
        return new $type();
    }
}
