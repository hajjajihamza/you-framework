<?php

declare(strict_types=1);

namespace YouRoute\Router;

use YouRoute\Attribute\Route;

/**
 * Classe RouteDispatcher
 *
 * Responsable de la distribution des requêtes HTTP vers les routes appropriées.
 *
 * Cette classe fournit les fonctionnalités suivantes :
 * - Correspondance d'URL (`match`) : Vérifie si une URL correspond à une route et extrait les paramètres.
 * - Exécution (`dispatch`) : Lance l'action associée à la route (Contrôleur ou Closure).
 *
 * Elle prend en charge les paramètres dynamiques dans les URL (ex: `/users/{id}`).
 */
readonly class RouteDispatcher
{
    /**
     * @param RouteCollection $routeCollection Collection contenant toutes les routes enregistrées.
     */
    public function __construct(private RouteCollection $routeCollection)
    {
    }

    /**
     * Tente de faire correspondre une URL et une méthode à une route enregistrée.
     *
     * Retourne un tableau contenant la route et les paramètres dynamiques si une correspondance est trouvée,
     * sinon retourne null.
     *
     * @param string $method Méthode HTTP (GET, POST, etc.)
     * @param string $url    URL à tester
     * @return array{route: Route, params: array}|null Retourne ['route' => Route, 'params' => array] ou null.
     */
    public function dispatch(string $method, string $url): ?array
    {
        // Récupère les routes pour la méthode donnée
        $routes = $this->routeCollection->all()[$method] ?? [];

        foreach ($routes as $route) {
            if (preg_match($route->getRegex(), $url, $matches)) {
                // On extrait les paramètres nommés
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return [
                    'route' => $route,
                    'params' => $params
                ];
            }
        }

        return null;
    }
}
