<?php

declare(strict_types=1);

namespace YouKernel\Component\Container;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

/**
 * Class Container
 *
 * Un conteneur de services léger avec support de l'auto-wiring.
 *
 * @package YouKernel
 */
class Container
{
    /**
     * @var array<string, mixed> Service instances storage.
     */
    private array $instances = [];

    /**
     * @var array<string, mixed> Definitions (factories or classes) storage.
     */
    private array $definitions = [];

    /**
     * Set a service or value.
     *
     * @param string $id       The service identifier (usually the class name).
     * @param mixed  $concrete The instance, factory closure, or class name.
     */
    public function set(string $id, mixed $concrete): void
    {
        $this->definitions[$id] = $concrete;
    }

    /**
     * Register an alias for a service.
     *
     * @param string $id
     * @param string $alias
     */
    public function alias(string $id, string $alias): void
    {
        $this->definitions[$alias] = $id;
    }

    /**
     * Check if a service is defined or instantiated.
     */
    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->definitions[$id]) || class_exists($id);
    }

    /**
     * Resolve and return a service.
     */
    public function get(string $id): mixed
    {
        // 1. If already instantiated, return it.
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // 2. If a definition exists
        if (isset($this->definitions[$id])) {
            $concrete = $this->definitions[$id];

            // If it's an alias (string pointing to another class/service)
            if (is_string($concrete) && $concrete !== $id && ($this->has($concrete) || class_exists($concrete))) {
                return $this->get($concrete);
            }

            // If it's a closure, execute it
            if ($concrete instanceof Closure) {
                $instance = $concrete($this);
                $this->instances[$id] = $instance;
                return $instance;
            }

            // If it's already an object, cache and return
            if (is_object($concrete)) {
                $this->instances[$id] = $concrete;
                return $concrete;
            }
        }

        // 3. Auto-wiring attempt if it's a valid class
        if (class_exists($id)) {
            $instance = $this->resolve($id);
            $this->instances[$id] = $instance;
            return $instance;
        }

        throw new RuntimeException("Service '$id' not found or cannot be resolved.");
    }

    /**
     * Resolve a class and its dependencies via Reflection.
     */
    private function resolve(string $class): object
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("Class '$class' is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        // If no constructor, instantiate directly
        if ($constructor === null) {
            return new $class();
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters(), $class);

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Call a method or closure while automatically injecting its arguments.
     *
     * @param callable $callable
     * @param array $args Manual arguments to override or supplement injection.
     * @return mixed
     */
    public function call(callable $callable, array $args = []): mixed
    {
        if (is_array($callable)) {
            $reflection = new ReflectionMethod($callable[0], $callable[1]);
        } else {
            $reflection = new \ReflectionFunction($callable(...));
        }

        $dependencies = $this->resolveDependencies($reflection->getParameters(), 'callable', $args);

        return call_user_func_array($callable, $dependencies);
    }

    /**
     * Resolve an array of reflection parameters.
     */
    private function resolveDependencies(array $parameters, string $context, array $args = []): array
    {
        $dependencies = [];

        foreach ($parameters as $param) {
            $name = $param->getName();

            // Override with manual arguments if provided
            if (array_key_exists($name, $args)) {
                $dependencies[] = $args[$name];
                continue;
            }

            $type = $param->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                    continue;
                }

                if ($param->allowsNull()) {
                    $dependencies[] = null;
                    continue;
                }

                throw new RuntimeException("Cannot resolve parameter '$name' in '$context'.");
            }

            $dependencies[] = $this->get($type->getName());
        }

        return $dependencies;
    }
}
