<?php

namespace DotDi\DependencyInjection;

use DotDi\Attributes\Inject;
use Exception;
use ReflectionClass;
use ReflectionFunction;
use ReflectionNamedType;

class ServiceProviderHelper
{
    /**
     * 
     * @param ServiceProvider $serviceProvider 
     * @param string[] $folders 
     * @return void 
     */
    public static function discover(ServiceProvider $serviceProvider, array $folders)
    {
        $files = [];
        foreach ($folders as $folder) {
            self::getDirContents($folder, $files);
        }

        foreach (array_keys($files) as $filename) {
            $pathinfo = pathinfo($filename);
            if (isset($pathinfo['extension']) && $pathinfo['extension'] === 'php') {
                include_once $filename;
            }
        }

        $types = get_declared_classes();
        foreach ($types as $class) {
            $rf = new ReflectionClass($class);
            if (!$rf->isInstantiable()) {
                continue;
            }
            $matched = false;
            foreach ($folders as $folder) {
                $matched = $matched || strpos($rf->getFileName(), $folder) === 0;
                if ($matched) {
                    // register type
                    $serviceProvider->addScoped($class);
                    break;
                }
            }
        }
    }

    static function getDirContents($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[$path] = true;
            } else if ($value != "." && $value != "..") {
                self::getDirContents($path, $results);
            }
        }

        return $results;
    }

    static function getDependencies($classOrCallable, ServiceProvider $services, ?array $params = null)
    {
        $arguments = [];
        if (is_string($classOrCallable)) // middleware class
        {
            $reflectionClass = new ReflectionClass($classOrCallable);
            $constructor = $reflectionClass->getConstructor();
            if ($constructor !== null) {
                $arguments = $constructor->getParameters();
            }
        } else if (is_callable($classOrCallable)) { // callable
            $rf = new ReflectionFunction($classOrCallable);
            $arguments = $rf->getParameters();
        } else {
            throw new Exception("Unsupported Type " . print_r($classOrCallable, true));
        }
        $dependencies = [];
        if (!empty($arguments)) {
            foreach ($arguments as $argument) {
                $argumentName = $argument->name;
                if ($argument->hasType()) {
                    /** @var ReflectionNamedType $namedType */
                    $namedType = $argument->getType();
                    if ($namedType instanceof ReflectionNamedType) {
                        $attributes = $argument->getAttributes(Inject::class);
                        $nextParams = null;
                        if (count($attributes) > 0) {
                            $injectAttribute = $attributes[0];
                            $nextParams = $injectAttribute->getArguments()[0];
                        }
                        $argumentValue = $services->get($namedType->getName(), $nextParams);
                        if ($argumentValue === null) {
                            if ($params !== null && key_exists($argumentName, $params)) {
                                $argumentValue = $params[$argumentName];
                            } else if (!$namedType->allowsNull()) {
                                throw new Exception("Can't resolve an argument $argumentName; $namedType");
                            }
                        }
                        $dependencies[] = $argumentValue;
                    } else {
                        throw new Exception("Can't get argument's type: $argumentName; $namedType");
                    }
                } else {
                    throw new Exception("Argument $argumentName doesn't have a type.");
                }
            }
        }
        return $dependencies;
    }
}
