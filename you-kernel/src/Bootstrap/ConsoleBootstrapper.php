<?php

namespace YouKernel\Bootstrap;

use YouConfig\Config;
use YouConsole\Helper\ListCommand;
use YouConsole\YouConsoleKernel;
use YouKernel\Component\Container\Container;

/**
 * Classe ConsoleBootstrapper
 *
 * Responsable du bootstrap du contexte Console (CLI).
 * Elle configure le kernel console, le répertoire
 * des commandes et les commandes système.
 *
 * @package YouKernel\Bootstrap
 * @author  Hamza Hajjaji <https://github.com/hajjvero>
 */
final class ConsoleBootstrapper
{
    /**
     * Initialise le kernel Console.
     *
     * @param Container $container Conteneur de dépendances
     *
     * @return YouConsoleKernel Kernel console prêt à l'exécution
     */
    public function boot(Container $container): YouConsoleKernel
    {
        $config = $container->get(Config::class);
        $projectDir = $container->get('project_dir');

        $commandsPath = $projectDir . '/' .
            ltrim($config->get('app.commands.resource', 'src/Command'), '/');

        $kernel = new YouConsoleKernel($container);
        $kernel->setCommandsDirectory($commandsPath);
        $kernel->registerCommand(new ListCommand());

        $container->set(YouConsoleKernel::class, $kernel);

        return $kernel;
    }
}