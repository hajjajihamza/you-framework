<?php

namespace YouKernel\Bootstrap;

use YouConfig\Config;
use YouHttpFoundation\Request;
use YouKernel\Component\Container\Container;
use YouKernel\Component\Controller\ControllerResolver;
use YouKernel\Component\Http\HttpKernel;
use YouRoute\YouRouteKernal;

/**
 * Classe HttpBootstrapper
 *
 * Gère le bootstrap du contexte HTTP du framework.
 * Elle initialise le système de routing, la résolution
 * des contrôleurs et le kernel HTTP.
 *
 * Cette classe prépare le cycle de vie d'une requête web.
 *
 * @package YouKernel\Bootstrap
 * @author  Hamza Hajjaji <https://github.com/hajjvero>
 */
final class HttpBootstrapper
{
    /**
     * Initialise et retourne le kernel HTTP.
     *
     * @param Container $container Conteneur de dépendances
     *
     * @return HttpKernel Kernel HTTP prêt à traiter les requêtes
     */
    public function boot(Container $container): HttpKernel
    {
        $config = $container->get(Config::class);
        $projectDir = $container->get('project_dir');

        $controllersPath = $projectDir . '/' .
            ltrim($config->get('app.routes.resource', 'src/Controller'), '/');

        $router = new YouRouteKernal($controllersPath);
        $resolver = new ControllerResolver($container);
        $request = Request::createFromGlobals();

        $kernel = new HttpKernel($router, $resolver, $request);

        $container->set(HttpKernel::class, $kernel);
        $container->set(YouRouteKernal::class, $router);
        $container->set(Request::class, $request);

        return $kernel;
    }
}