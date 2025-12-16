<?php

declare(strict_types=1);

namespace YouKernel\Component\Http;

use JsonException;
use Throwable;
use YouHttpFoundation\Request;
use YouHttpFoundation\Response;
use YouKernel\Component\Controller\ControllerResolver;
use YouRoute\YouRouteKernal;

/**
 * Class HttpKernel
 *
 * Le noyau HTTP du framework. Il orchestre le traitement de la requête :
 * Request -> Routing -> Controller -> Response.
 *
 * @package YouKernel
 */
class HttpKernel
{
    /**
     * @var YouRouteKernal Le routeur principal.
     */
    private YouRouteKernal $router;

    /**
     * @var ControllerResolver Le résolveur de contrôleur.
     */
    private ControllerResolver $resolver;

    /**
     * Constructeur.
     *
     * @param YouRouteKernal     $router
     * @param ControllerResolver $resolver
     */
    public function __construct(YouRouteKernal $router, ControllerResolver $resolver)
    {
        $this->router = $router;
        $this->resolver = $resolver;
    }

    /**
     * Traite la requête HTTP et retourne une réponse.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        try {
            // 1. Routing
            // On récupère la méthode et l'URI (path info) depuis la Request
            $method = $request->getMethod();
            $path = $request->getPath();

            $match = $this->router->dispatch($method, $path);

            // Si aucune route ne correspond
            if ($match === null) {
                return new Response("Not Found", 404);
            }

            // 2. Exécution du contrôleur via le Resolver
            // Le resolver injecte la Request et les paramètres de route
            $result = $this->resolver->resolve($match, $request);

            // 3. Conversion du résultat en objet Response
            return $this->transformToResponse($result);

        } catch (Throwable $e) {
            // Gestion basique des erreurs (500)
            // Dans un framework plus complet, on aurait un ExceptionHandler dédié
            return new Response(
                "Internal Server Error: " . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Normalise le retour du contrôleur en objet Response.
     *
     * @param mixed $result
     * @return Response
     * @throws JsonException
     */
    private function transformToResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            // Conversion automatique en JSON
            return new Response(
                json_encode($result, JSON_THROW_ON_ERROR),
                200,
                ['Content-Type' => ['application/json']]
            );
        }

        // Par défaut, on traite comme une chaîne de caractères
        return new Response((string) $result);
    }
}
