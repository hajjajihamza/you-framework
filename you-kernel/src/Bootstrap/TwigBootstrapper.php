<?php

namespace YouKernel\Bootstrap;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use YouConfig\Config;
use YouKernel\Component\Container\Container;

/**
 * Classe TwigBootstrapper
 *
 * Gère l'initialisation du moteur de template Twig.
 * Elle configure le loader de fichiers et l'environnement Twig
 * pour qu'ils soient injectés dans le conteneur de services.
 *
 * @package YouKernel\Bootstrap
 * @author  Hamza Hajjaji <https://github.com/hajjvero>
 */
final class TwigBootstrapper
{
    /**
     * Initialise et retourne l'environnement Twig.
     *
     * @param Container $container Conteneur de dépendances
     *
     * @return Environment Instance de l'environnement Twig configuré
     */
    public function boot(Container $container): Environment
    {
        /** @var Config $config */
        $config = $container->get(Config::class);
        $projectDir = $container->get('project_dir');

        // Définition du chemin des templates (défaut: templates/)
        $templatesPath = $projectDir . '/' . ltrim($config->get('app.twig.path', 'templates'), '/');

        $loader = new FilesystemLoader($templatesPath);

        // Configuration de l'environnement Twig (Cache désactivé explicitement)
        $twig = new Environment($loader, [
            'cache' => false,
            'debug' => $config->get('app.debug', false),
        ]);

        // Enregistrement dans le conteneur
        $container->set(Environment::class, $twig);
        $container->set(FilesystemLoader::class, $loader);

        return $twig;
    }
}