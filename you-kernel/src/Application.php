<?php

namespace YouKernel;

use JetBrains\PhpStorm\NoReturn;
use YouKernel\Bootstrap\{
    ProjectResolver,
    EnvironmentLoader,
    ContainerBootstrapper,
    HttpBootstrapper,
    ConsoleBootstrapper,
    TwigBootstrapper,
    OrmBootstrapper
};
use YouKernel\Runner\{HttpRunner, ConsoleRunner};
use YouConsole\YouConsoleKernel;
use YouKernel\Component\Container\Container;
use YouKernel\Component\Http\HttpKernel;

/**
 * Classe Application
 *
 * Point d'entrée principal du framework you-framework.
 * Cette classe agit comme une façade orchestrant
 * l'initialisation du noyau (Kernel) et le lancement
 * des contextes HTTP et Console.
 *
 * @author  Hamza Hajjaji <https://github.com/hajjvero>
 */
final class Application
{
    /**
     * Conteneur de dépendances du framework.
     *
     * @var mixed
     */
    private Container $container;

    /**
     * Kernel HTTP.
     *
     * @var mixed|null
     */
    private HttpKernel $httpKernel;

    /**
     * Kernel Console.
     *
     * @var mixed|null
     */
    private YouConsoleKernel $consoleKernel;

    /**
     * Constructeur de l'application.
     *
     * @param string|null $projectDir Répertoire du projet
     * @param string      $configDir  Répertoire de configuration
     */
    public function __construct(?string $projectDir = null, string $configDir = 'config')
    {
        // Récupération du répertoire du projet
        $resolver = new ProjectResolver();
        $projectDir = $resolver->resolve($projectDir);

        // Chargement de l'environnement
        new EnvironmentLoader()->load($projectDir);

        // Initialisation du conteneur
        $this->container = new ContainerBootstrapper()->boot($projectDir, sprintf('%s/%s', $projectDir, $configDir));
    }

    /**
     * Lance l'application en mode HTTP.
     *
     * @return void
     */
    #[NoReturn]
    public function runHttp(): void
    {
        // Initialisation du Kernel HTTP
        $this->httpKernel ??= new HttpBootstrapper()->boot($this->container);

        // Initialisation de Twig
        new TwigBootstrapper()->boot($this->container);

        // Initialisation de l'ORM
        new OrmBootstrapper()->boot($this->container);

        // Lancement du Kernel
        new HttpRunner()->run($this->httpKernel);
    }

    /**
     * Lance l'application en mode Console.
     *
     * @param array $argv Arguments CLI
     *
     * @return void
     */
    #[NoReturn]
    public function runConsole(array $argv): void
    {
        // Initialisation du Kernel Console
        $this->consoleKernel ??= new ConsoleBootstrapper()->boot($this->container);

        // Initialisation de l'ORM
        new OrmBootstrapper()->boot($this->container);

        // Lancement du Kernel
        new ConsoleRunner()->run($this->consoleKernel, $argv);
    }
}