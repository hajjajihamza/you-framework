<?php

declare(strict_types=1);

namespace YouRoute\Router;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use YouHttpFoundation\ResponseInterface;
use YouRoute\Attribute\Route;

/**
 * Class RouteResolver
 *
 * Scans directories for controllers and resolves routes defined via attributes.
 *
 * @package YouRoute\Router
 */
readonly class RouteResolver
{
    /**
     * RouteResolver constructor.
     *
     * @param RouteCollection $routeCollection The collection where resolved routes will be stored.
     */
    public function __construct(
        private RouteCollection $routeCollection
    ) {
    }

    /**
     * Loads routes from a specified directory by scanning for controller classes.
     *
     * @param string $resourceDir The directory path to scan for PHP files.
     *
     * @throws ReflectionException If a class cannot be reflected.
     * @throws RuntimeException If the directory does not exist.
     */
    public function loadRoutesFromDirectory(string $resourceDir): void
    {
        $controllers = discover_classes($resourceDir);

        $reflections = array_map(
            static fn(string $controller): ReflectionClass => new ReflectionClass($controller),
            $controllers
        );

        array_map(fn(ReflectionClass $reflection) => $this->processClassReflection($reflection), $reflections);
    }

    /**
     * Processes a single class reflection.
     *
     * @param ReflectionClass $reflection The class reflection to process.
     *
     * @throws ReflectionException If the class is abstract or contains invalid route definitions.
     */
    private function processClassReflection(ReflectionClass $reflection): void
    {
        // Check if class is instantiable
        if (!$reflection->isInstantiable()) {
            // Only throw if it seemingly attempts to define routes or is expected to be a controller?
            // Existing logic: throws if NOT instantiable.
            // Note: Abstract classes might simply be ignored in many frameworks, but following original requirement to throw.
            // However, iterating ALL files in a dir might hit abstract parents.
            // PROPOSAL: Only throw if it HAS a Route attribute but is abstract?
            // For now, adhering to strict original logic but refining message.
            throw new ReflectionException(sprintf(
                "The class %s is abstract or not instantiable and cannot be used as a route controller.",
                $reflection->getName()
            ));
        }

        // Get class-level Route attribute
        $routeController = $this->getAttributeInstance($reflection);

        // Process methods
        foreach ($reflection->getMethods() as $method) {
            /** @var ?Route $routeMethod */
            $routeMethod = $this->getAttributeInstance($method);

            if (!$routeMethod) {
                continue;
            }

            $this->registerRoute($reflection, $method, $routeController, $routeMethod);
        }
    }

    /**
     * Registers a route for a specific method.
     *
     * @param ReflectionClass $class The controller class.
     * @param ReflectionMethod $method The action method.
     * @param Route|null $classRoute The class-level route attribute (prefix), if any.
     * @param Route $methodRoute The method-level route attribute.
     *
     * @throws ReflectionException If the method is invalid (non-public or wrong return type).
     */
    private function registerRoute(
        ReflectionClass $class,
        ReflectionMethod $method,
        ?Route $classRoute,
        Route $methodRoute
    ): void {
        // Set action [Controller::class, method]
        $methodRoute->setAction([$class->getName(), $method->getName()]);

        // Validate Validation: Method must be public
        if (!$method->isPublic()) {
            throw new ReflectionException(sprintf(
                "The method %s::%s must be public to be used as a route action.",
                $class->getName(),
                $method->getName()
            ));
        }

        // Validate Return Type: Must implement ResponseInterface
        // Note: Using resolving getName() on return type.
        $returnType = $method->getReturnType();
        if (!$returnType instanceof \ReflectionNamedType || !is_a($returnType->getName(), ResponseInterface::class, true)) {
            throw new ReflectionException(sprintf(
                "The method %s::%s must return an instance of %s.",
                $class->getName(),
                $method->getName(),
                ResponseInterface::class
            ));
        }

        // Add to collection with prefix handling
        $this->routeCollection->add(
            $this->routeCollection->prefix($classRoute, $methodRoute)
        );
    }

    /**
     * Helper to retrieve a specific attribute instance from a reflection object.
     *
     * @param \ReflectionClass|ReflectionMethod $reflector
     * @return mixed|null
     */
    private function getAttributeInstance(\ReflectionClass|ReflectionMethod $reflector): mixed
    {
        $attributes = $reflector->getAttributes(Route::class);
        if (empty($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }
}
