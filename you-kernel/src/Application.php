<?php

declare(strict_types=1);

namespace YouKernel;

use ReflectionException;
use YouHttpFoundation\Request;
use YouKernel\Container\Container;
use YouKernel\Controller\ControllerResolver;
use YouKernel\Http\HttpKernel;
use YouRoute\YouRouteKernal;

/**
 * Class Application
 *
 * Point d'entrée principal du framework.
 * Initialise les composants et lance l'exécution de la requête.
 *
 * @package YouKernel
 */
class Application
{
    /** @var HttpKernel */
    private HttpKernel $kernel;

    /**
     * @param string|null $projectDir La racine du projet. Si null, tente de la deviner.
     * @throws ReflectionException
     */
    public function __construct(?string $projectDir = null)
    {
        // 1. Détermination de la racine du projet
        if ($projectDir === null) {
            // Suppose que le point d'entrée est public/index.php
            // On remonte de 2 niveaux : public/index.php -> public -> root
            $scriptPath = $_SERVER['SCRIPT_FILENAME'] ?? null;
            if ($scriptPath) {
                $projectDir = dirname($scriptPath, 2);
            } else {
                // Fallback ou environnement CLI
                $projectDir = getcwd();
            }
        }

        // 2. Initialisation du container
        $container = new Container();

        $controllersPath = $projectDir . '/src/Controller';

        $router = new YouRouteKernal($controllersPath);

        // Enregistrement des services cœurs
        $container->set(YouRouteKernal::class, $router);
        $container->set(Container::class, $container);

        // 3. Initialisation du résolveur avec le conteneur
        $resolver = new ControllerResolver($container);

        // 4. Initialisation du Kernel
        $this->kernel = new HttpKernel($router, $resolver);
    }

    /**
     * Démarre l'application.
     * Crée la requête, la traite et envoie la réponse.
     */
    public function runHttp(): void
    {
        // 1. Création de la requête depuis les globales
        $request = Request::createFromGlobals();

        // 2. Traitement par le Kernel
        $response = $this->kernel->handle($request);

        // 3. Envoi de la réponse
        $response->send();
    }
}
