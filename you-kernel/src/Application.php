<?php

declare(strict_types=1);

namespace YouKernel;

use ReflectionException;
use YouConsole\Helper\ListCommand;
use YouConsole\YouConsoleKernel;
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
    /** @var HttpKernel|null */
    private ?HttpKernel $kernel = null;

    /** @var Container */
    private Container $container;

    /** @var string */
    private string $projectDir;

    /** @var string|null */
    private ?string $controllerPath = null;

    /** @var string|null */
    private ?string $commandPath = null;

    /** @var bool */
    private bool $httpBooted = false;

    /** @var bool */
    private bool $consoleBooted = false;

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

        $this->projectDir = $projectDir;

        // 2. Initialisation du container
        $this->container = new Container();
        $this->container->set('project_dir', $this->projectDir);
        $this->container->set(Container::class, $this->container);
    }

    /**
     * Permet de définir un chemin personnalisé pour les contrôleurs.
     *
     * @param string $path
     * @return self
     */
    public function withControllerPath(string $path): self
    {
        $this->controllerPath = $path;
        return $this;
    }

    /**
     * Permet de définir un chemin personnalisé pour les commandes.
     *
     * @param string $path
     * @return self
     */
    public function withCommandPath(string $path) : self {
        $this->commandPath = $path;
        return $this;
    }

    /**
     * Récupère le conteneur de services pour une configuration avancée.
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Démarre le kernel et initialise les composants.
     *
     * @throws ReflectionException
     */
    public function bootHttp(): self
    {
        if ($this->httpBooted) {
            return $this;
        }

        // Détermination du chemin des contrôleurs ou fallback sur src/Controller
        $controllersPath = $this->controllerPath ?? ($this->projectDir . '/src/Controller');

        $router = new YouRouteKernal($controllersPath);

        // Enregistrement des services cœurs
        $this->container->set(YouRouteKernal::class, $router);

        // Initialisation du résolveur avec le conteneur
        $resolver = new ControllerResolver($this->container);

        // Initialisation du Kernel
        $this->kernel = new HttpKernel($router, $resolver);

        $this->httpBooted = true;

        return $this;
    }

    /**
     * Démarre le kernel console et initialise les composants.
     *
     */
    public function bootConsole(): YouConsoleKernel
    {
        // Détermination du chemin des commandes ou fallback sur src/Command
        $commandsPath = $this->commandPath ?? ($this->projectDir . '/src/Command');

        // Initialisation du Kernel Console
        $consoleKernal = new YouConsoleKernel($this->container)
            ->setCommandsDirectory($commandsPath)
        ;

        // Enregistrement des services cœurs
        $this->container->set(YouConsoleKernel::class, $consoleKernal);

        // Enregistrement de la commande ListCommand
        $consoleKernal->registerCommand(new ListCommand());

        $this->consoleBooted = true;

        return $consoleKernal;
    }

    /**
     * Démarre l'application.
     * Crée la requête, la traite et envoie la réponse.
     */
    public function runHttp(): void
    {
        if (!$this->httpBooted) {
            $this->bootHttp();
        }

        // 1. Création de la requête depuis les globales
        $request = Request::createFromGlobals();

        // 2. Traitement par le Kernel
        $response = $this->kernel->handle($request);

        // 3. Envoi de la réponse
        $response->send();
    }

    public function runConsole(): void
    {
        global $argv;
        if (!$this->consoleBooted) {
            $this->bootConsole()->run($argv);
        }
    }
}
