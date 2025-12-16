<?php

namespace YouKernel\Bootstrap;

use YouConfig\Config;
use YouKernel\Component\Container\Container;

/**
 * Classe ContainerBootstrapper
 *
 * Responsable de l'initialisation du conteneur
 * d'injection de dépendances (IoC Container).
 *
 * Elle enregistre les services fondamentaux du framework :
 * - Container lui-même
 * - Répertoire du projet
 * - Gestionnaire de configuration
 *
 * @package YouKernel\Bootstrap
 * @author Hamza Hajjaji <https://github.com/hajjvero>
 */
final class ContainerBootstrapper
{
    /**
     * Initialise et configure le container de services.
     *
     * @param string $projectDir Chemin absolu du projet
     * @param string $configPath Chemin du répertoire de configuration
     *
     * @return Container Instance du conteneur configuré
     */
    public function boot(string $projectDir, string $configPath): Container
    {
        $container = new Container();

        $container->set('project_dir', $projectDir);
        $container->set(Container::class, $container);

        $config = new Config($configPath);
        $container->set(Config::class, $config);

        return $container;
    }
}