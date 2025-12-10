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
            // Vérifie la correspondance du chemin
            $params = $this->matchPath($route->getPath(), $url);

            // Si params est un tableau, c'est que ça matche
            if (is_array($params)) {
                return [
                    'route' => $route,
                    'params' => $params
                ];
            }
        }

        return null;
    }

    /**
     * Compare un motif de route à une URL réelle et extrait les paramètres.
     *
     * @param string $pattern Motif de la route (ex: '/users/{id}')
     * @param string $url     URL réelle (ex: '/users/42/')
     * @return bool|array     Retourne les paramètres extraits (array) si correspondance, sinon false.
     */
    private function matchPath(string $pattern, string $url): bool|array
    {
        $patternSegments = explode('/', $pattern);
        $urlSegments = explode('/', $url);

        // Vérifie le nombre de segments
        if (count($patternSegments) !== count($urlSegments)) {
            return false;
        }

        $params = [];

        // Compare chaque segment
        foreach ($patternSegments as $index => $patternSegment) {
            // Vérifie si le segment est un paramètre dynamique (ex: {id})
            if (preg_match('/^\{(\w+)\}$/', $patternSegment, $match)) {
                // Extrait le nom du paramètre et sa valeur
                $params[$match[1]] = $urlSegments[$index];
            } elseif ($patternSegment !== $urlSegments[$index]) {
                // Correspondance exacte échouée pour ce segment
                return false;
            }
        }

        return $params;
    }
}
