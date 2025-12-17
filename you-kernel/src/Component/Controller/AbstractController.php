<?php

declare(strict_types=1);

namespace YouKernel\Component\Controller;

use Twig\Environment;
use YouHttpFoundation\Response;
use YouKernel\Component\Container\Container;
use YouRoute\YouRouteKernal;

/**
 * Class AbstractController
 *
 * Classe de base pour tous les contrôleurs du framework.
 * Fournit des méthodes utilitaires pour faciliter la création de réponses,
 * la redirection et l'accès au conteneur de services.
 *
 * @package YouKernel\Controller
 */
abstract class AbstractController
{
    /**
     * @var Container Le conteneur de services.
     */
    protected Container $container;

    /**
     * Injection du conteneur de services.
     * Cette méthode est appelée automatiquement par le ControllerResolver.
     *
     * @param Container $container
     */
    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    /**
     * Génère une réponse HTML à partir d'une vue.
     *
     * @param string $view   Le chemin vers le fichier de vue (relatif au dossier templates).
     * @param array  $data   Les données à passer à la vue.
     * @param int    $status Le code de statut HTTP (200 par défaut).
     * @return Response
     */
    protected function render(string $view, array $data = [], int $status = 200): Response
    {
        /** @var Environment $twig */
        $twig = $this->container->get(Environment::class);

        return new Response($twig->render($view, $data), $status);
    }

    /**
     * Génère une réponse JSON.
     *
     * @param mixed $data    Les données à encoder en JSON.
     * @param int   $status  Le code de statut HTTP (200 par défaut).
     * @param array $headers Les en-têtes HTTP supplémentaires.
     * @return Response
     */
    protected function renderJson(mixed $data, int $status = 200, array $headers = []): Response
    {
        $content = json_encode($data);
        $headers['Content-Type'] = 'application/json';

        return new Response($content, $status, $headers);
    }

    /**
     * Redirige vers une URL donnée.
     *
     * @param string $url    L'URL de destination.
     * @param int    $status Le code de statut HTTP (302 par défaut).
     * @return Response
     */
    protected function redirectTo(string $url, int $status = 302): Response
    {
        return new Response('', $status, ['Location' => $url]);
    }

    /**
     * Redirige vers une route spécifique.
     *
     * @param string $routeName Le nom de la route.
     * @param array  $parameters Les paramètres de la route.
     * @param int    $status    Le code de statut HTTP (302 par défaut).
     * @return Response
     */
    protected function redirectToRoute(string $routeName, array $parameters = [], int $status = 302): Response
    {
        /** @var YouRouteKernal $router */
        $router = $this->container->get(YouRouteKernal::class);
        $url = $router->generateUrl($routeName, $parameters);

        return $this->redirectTo($url, $status);
    }
}
