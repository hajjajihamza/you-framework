<?php

namespace YouKernel\Bootstrap;

use YouConfig\Config;
use YouConsole\Helper\ListCommand;
use YouConsole\YouConsoleKernel;
use YouKernel\Component\Container\Container;
use YouMake\Command\Generator\{ControllerMakeCommand, EntityMakeCommand, CommandMakeCommand, MigrationMakeCommand};


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
        $kernel = new YouConsoleKernel($container);

        $kernel->registerCommand(
            new ListCommand(),
            new CommandMakeCommand($container),
            new ControllerMakeCommand($container),
            new EntityMakeCommand($container),
            new MigrationMakeCommand($container)
        );

        $container->set(YouConsoleKernel::class, $kernel);

        return $kernel;
    }
}