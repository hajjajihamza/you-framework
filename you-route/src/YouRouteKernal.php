<?php

declare(strict_types=1);

namespace YouRoute;

use ReflectionException;
use RuntimeException;
use YouRoute\Attribute\Route;
use YouRoute\Router\RouteCollection;
use YouRoute\Router\RouteDispatcher;
use YouRoute\Router\RouteResolver;

/**
 * Class YouRouteKernal
 *
 * Cette classe sert de point d'entrée principal pour initialiser et démarrer la bibliothèque YouRoute.
 * Elle orchestre le chargement des routes, la résolution de la requête HTTP entrante
 * et l'exécution de l'action appropriée (contrôleur).
 *
 * Idéalement utilisée dans le fichier d'entrée de l'application (ex: index.php).
 *
 * @package YouRoute
 */
final class YouRouteKernal
{
    /**
     * @var RouteCollection Collection contenant toutes les routes de l'application.
     */
    private RouteCollection $routeCollection;

    /**
     * @var RouteDispatcher Le système de distribution des routes (matching).
     */
    private RouteDispatcher $dispatcher;

    /**
     * @var RouteResolver Le résolveur responsable de charger les routes depuis les fichiers.
     */
    private RouteResolver $resolver;

    /**
     * Constructeur de YouRouteKernal.
     *
     * Initialise les composants internes et charge automatiquement les routes
     * depuis le répertoire spécifié.
     *
     * @param string $controllersPath Le chemin absolu vers le répertoire contenant les contrôleurs.
     * @throws RuntimeException|ReflectionException Si le répertoire spécifié n'existe pas.
     */
    public function __construct(string $controllersPath)
    {
        // Initialisation de la collection de routes
        $this->routeCollection = new RouteCollection();

        // Initialisation du résolveur avec la collection
        $this->resolver = new RouteResolver($this->routeCollection);

        // Chargement des routes via les attributs PHP dans le dossier donné
        $this->resolver->loadRoutesFromDirectory($controllersPath);

        // Initialisation du dispatcher avec les routes chargées
        $this->dispatcher = new RouteDispatcher($this->routeCollection);
    }

    /**
     * Dispatche une requête HTTP.
     *
     * @param string $method Méthode HTTP (GET, POST, etc.)
     * @param string $url    URL de la requête
     * @return array{route: Route, params: array}|null Retourne ['route' => Route, 'params' => array] ou null.
     */
    public function dispatch(string $method, string $url): ?array
    {
        return $this->dispatcher->dispatch($method, $url);
    }

    
    /**
     * Génère une URL à partir du nom d'une route.
     *
     * Permet de construire une URL complète en remplaçant les paramètres dynamiques
     * et en ajoutant les paramètres de requête (query string).
     *
     * @param string $routeName Le nom de la route (attribut name dans #[Route]).
     * @param array  $params    Paramètres de route (ex: ['id' => 1]) et de query string.
     * @return string L'URL générée.
     * @throws RuntimeException Si la route n'existe pas.
     */
    public function generateUrl(string $routeName, array $params = []): string
    {
        // Récupérer la route par son nom depuis la collection
        $route = $this->routeCollection->getRouteByName($routeName);

        if (!$route) {
            throw new RuntimeException("Route '{$routeName}' not found.");
        }

        $path = $route->getPath();
        $queryParams = [];

        // Remplacement des paramètres dynamiques dans le chemin
        foreach ($params as $key => $value) {
            if (str_contains($path, '{' . $key . '}')) {
                $path = str_replace('{' . $key . '}', (string) $value, $path);
            } else {
                // Si le paramètre n'est pas dans le chemin, il va dans la query string
                $queryParams[$key] = $value;
            }
        }

        // Ajout de la query string si nécessaire
        if (!empty($queryParams)) {
            $path .= '?' . http_build_query($queryParams);
        }

        return $path;
    }
}