<?php

declare(strict_types=1);

namespace YouKernel\Component\Controller;

use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use YouHttpFoundation\Request;
use YouKernel\Component\Container\Container;
use YouRoute\Attribute\Route;

/**
 * Class ControllerResolver
 *
 * Responsable de l'exécution du contrôleur et de l'injection des dépendances (arguments).
 *
 * @package YouKernel
 */
class ControllerResolver
{
    /**
     * @var Container
     */
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Exécute le contrôleur associé à la route correspondante.
     *
     * @param array{route: Route, params: array} $match Le résultat du dispatching (route + params).
     * @return mixed Le retour du contrôleur (Response, string, array, etc.).
     * @throws RuntimeException
     */
    public function resolve(array $match): mixed
    {
        $route = $match['route'];
        $routeParams = $match['params'];
        $action = $route->getAction();

        if (empty($action) || !is_array($action) || count($action) !== 2) {
            throw new RuntimeException("La route '{$route->getName()}' n'a pas d'action valide définie.");
        }

        [$controllerClass, $methodName] = $action;

        if (!class_exists($controllerClass)) {
            throw new RuntimeException("Le contrôleur '{$controllerClass}' n'existe pas.");
        }

        // Instanciation du contrôleur via le conteneur (injection de dépendances)
        $controllerInstance = $this->container->get($controllerClass);

        // Injection du conteneur si le contrôleur étend AbstractController
        if ($controllerInstance instanceof AbstractController) {
            $controllerInstance->setContainer($this->container);
        }

        if (!method_exists($controllerInstance, $methodName)) {
            throw new RuntimeException("La méthode '{$methodName}' n'existe pas dans le contrôleur '{$controllerClass}'.");
        }

        // Execute action with dependency injection and route parameters
        return $this->container->call([$controllerInstance, $methodName], $routeParams);
    }
}
