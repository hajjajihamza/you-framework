<?php

declare(strict_types=1);

namespace YouKernel;

use Dotenv\Dotenv;
use ReflectionException;
use RuntimeException;
use YouConfig\Config;
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

    /** @var Config */
    private Config $config;

    /** @var string */
    private string $projectDir;

    /** @var string */
    private string $configPath;

    /** @var bool */
    private bool $httpBooted = false;

    /** @var bool */
    private bool $consoleBooted = false;

    /**
     * @param string|null $projectDir La racine du projet. Si null, tente de la deviner.
     * @throws ReflectionException
     */
    public function __construct(?string $projectDir = null, string $configDir = 'config')
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
        $this->configPath = $this->projectDir . $configDir;

        if (!is_dir($this->configPath)) {
            throw new RuntimeException('Le dossier "config" n\'existe pas. Veuillez le créer.');
        }


        // 2. Initialisation du container
        $this->container = new Container();
        $this->container->set('project_dir', $this->projectDir);
        $this->container->set(Container::class, $this->container);
        $this->config = new Config($this->configPath);

        $this->container->set(Config::class, $this->config);

        $dotenv = Dotenv::createImmutable($this->projectDir);
        $dotenv->load();
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

        $router = new YouRouteKernal($this->config->get('app.routes.resource',  '/src/Controller'));

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
            ->setCommandsDirectory($this->config->get('app.commands.resource',  '/src/Command'));

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
            exit($this->bootConsole()->run($argv)); // Exit code
        }
    }
}
