<?php

// testing DI
require __DIR__ . '/../vendor/autoload.php';

use Tests\Controllers\ControllersMark;
use Tests\Controllers\TestController;
use DotDi\DependencyInjection\Container;
use DotDi\DependencyInjection\ServiceProvider;
use DotDi\DependencyInjection\ServiceProviderHelper;
use Tests\Application\BarService;
use Tests\Application\FooService;
use Tests\Application\GenericService;
use Tests\Application\HttpContext;
use Tests\Application\ITaxService;
use Tests\Application\TaxService;
use Tests\Application\TestService;

function buildServiceProvider(): ServiceProvider
{
    $services = new ServiceProvider();
    $services->addSingleton(FooService::class);
    $services->addTransient(BarService::class);
    $services->addScoped(ITaxService::class, TaxService::class);
    $services->addScoped(GenericService::class);
    $services->addScoped(TestService::class);
    ServiceProviderHelper::discover($services, [ControllersMark::folder()]);
    return $services;
}

function resolveDependencies(Container $scope)
{
    $dependencies = [];
    $dependencies[] = $scope->serviceProvider->get(FooService::class);
    $dependencies[] = $scope->serviceProvider->get(FooService::class);
    $dependencies[] = $scope->serviceProvider->get(BarService::class);
    $dependencies[] = $scope->serviceProvider->get(BarService::class);
    $dependencies[] = $scope->serviceProvider->get(TestService::class);
    $dependencies[] = $scope->serviceProvider->get(TestService::class);
    $dependencies[] = $scope->serviceProvider->get(TestController::class);
    $dependencies[] = $scope->serviceProvider->get(TestController::class);
    $dependencies[] = $scope->serviceProvider->get(ITaxService::class);
    $dependencies[] = $scope->serviceProvider->get(ITaxService::class);
    /** @var GenericService $topicsRepository */
    $topicsRepository = $scope->serviceProvider->get(
        GenericService::class,
        [
            'type' => GuideTopicEntity::class,
            'dbMap' => GuideTopicEntityDbMap::class
        ]
    );
    $dependencies[] = $topicsRepository;
    $dependencies[] = $scope->serviceProvider->get(HttpContext::class);
    $dependencies[] = $scope->serviceProvider->get(HttpContext::class);
    $dependencies[] = $scope->serviceProvider->get(Container::class);
    return $dependencies;
}

$services = buildServiceProvider();
$scope = $services->createScope();
$dependencies = resolveDependencies($scope);

// $services = buildServiceProvider();
$requestScope = $services->createScope();
$requestScope->serviceProvider->set(HttpContext::class, new HttpContext());

$dependencies2 = resolveDependencies($requestScope);


var_dump($services !== $scope->serviceProvider);
var_dump($services !== $requestScope->serviceProvider);
var_dump($scope !== $requestScope);
var_dump($scope->serviceProvider->get(FooService::class) === $requestScope->serviceProvider->get(FooService::class));
var_dump($scope->serviceProvider->get(BarService::class) !== $requestScope->serviceProvider->get(BarService::class));
var_dump($scope->serviceProvider->get(BarService::class) !== $scope->serviceProvider->get(BarService::class));
var_dump($scope->serviceProvider->get(ITaxService::class) !== $requestScope->serviceProvider->get(ITaxService::class));
var_dump($scope->serviceProvider->get(ITaxService::class) === $scope->serviceProvider->get(ITaxService::class));