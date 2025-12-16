<?php

declare(strict_types=1);

namespace YouKernel\Component\Container;

use ReflectionClass;
use ReflectionException;
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
     * @var array<string, mixed> Stockage des instances de services.
     */
    private array $instances = [];

    /**
     * @var array<string, mixed> Stockage des définitions (factories ou classes).
     */
    private array $definitions = [];

    /**
     * Enregistre un service ou une valeur.
     *
     * @param string $id       L'identifiant du service (souvent le nom de la classe).
     * @param mixed  $concrete L'instance, la closure factory, ou le nom de la classe.
     */
    public function set(string $id, mixed $concrete): void
    {
        $this->definitions[$id] = $concrete;
    }

    /**
     * Vérifie si un service est défini ou instancié.
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->definitions[$id]) || class_exists($id);
    }

    /**
     * Résout et retourne un service.
     *
     * @param string $id L'identifiant du service.
     * @return mixed L'instance du service.
     * @throws RuntimeException|ReflectionException
     */
    public function get(string $id): mixed
    {
        // 1. Si déjà instancié (Singleton par défaut), on retourne
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // 2. Si une définition existe
        if (isset($this->definitions[$id])) {
            $concrete = $this->definitions[$id];

            // Si c'est une closure, on l'exécute pour obtenir l'instance
            if ($concrete instanceof \Closure) {
                $this->instances[$id] = $concrete($this);
            } else {
                $this->instances[$id] = $concrete;
            }

            return $this->instances[$id];
        }

        // 3. Tentative d'auto-wiring si c'est une classe valide
        if (class_exists($id)) {
            $instance = $this->resolve($id);
            $this->instances[$id] = $instance;
            return $instance;
        }

        throw new RuntimeException("Service '$id' not found or cannot be resolved.");
    }

    /**
     * Résout une classe et ses dépendances via Reflection.
     *
     * @param string $class
     * @return object
     * @throws ReflectionException
     */
    private function resolve(string $class): object
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("Class '$class' is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        // Si pas de constructeur, on instancie directement
        if ($constructor === null) {
            return new $class();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $param) {
            $type = $param->getType();

            // On ne gère que les types nommés (classes/interfaces) pour l'instant
            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                // Si une valeur par défaut existe, on l'utilise
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                    continue;
                }

                throw new RuntimeException("Cannot resolve primitive parameter '{$param->getName()}' in class '$class'.");
            }

            // Récursion pour résoudre la dépendance
            $dependencyClass = $type->getName();
            $dependencies[] = $this->get($dependencyClass); // Réutilise get() pour bénéficier du cache singleton
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
