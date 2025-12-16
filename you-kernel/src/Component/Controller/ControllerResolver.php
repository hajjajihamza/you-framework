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
     * @param Request $request La requête HTTP actuelle.
     * @return mixed Le retour du contrôleur (Response, string, array, etc.).
     * @throws ReflectionException|RuntimeException
     */
    public function resolve(array $match, Request $request): mixed
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

        // Résolution des arguments de la méthode via Reflection
        $args = $this->resolveArguments($controllerClass, $methodName, $request, $routeParams);

        // Appel de la méthode
        return $controllerInstance->$methodName(...$args);
    }

    /**
     * Résout les arguments à passer à la méthode du contrôleur.
     *
     * @param string $class Nom de la classe du contrôleur.
     * @param string $method Nom de la méthode.
     * @param Request $request La requête HTTP.
     * @param array $routeParams Les paramètres extraits de la route.
     * @return array La liste des arguments ordonnée.
     * @throws ReflectionException
     */
    private function resolveArguments(string $class, string $method, Request $request, array $routeParams): array
    {
        $reflection = new ReflectionMethod($class, $method);
        $parameters = $reflection->getParameters();
        $args = [];

        foreach ($parameters as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            // Injection les paramètres typés et non built-in (string, int, bool, etc.)
            if ($paramType instanceof \ReflectionNamedType && !$paramType->isBuiltin()) {
                // Injection de la Request si typée
                if ($paramType->getName() === Request::class) {
                    $args[] = $request;
                    continue;
                }

                // Injection other dependencies
                if ($this->container->has($paramType->getName())) {
                    $args[] = $this->container->get($paramType->getName());
                    continue;
                }
            }

            // Injection des paramètres de route (ex: {id})
            if (array_key_exists($paramName, $routeParams)) {
                $args[] = $routeParams[$paramName];
                continue;
            }

            // Valeur par défaut si disponible
            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            }
        }

        return $args;
    }
}
