<?php

declare(strict_types=1);

namespace YouRoute\Attribute;

use Attribute;
use InvalidArgumentException;

/**
 * Attribut permettant de définir une route directement sur une méthode de contrôleur ou une classe.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Route
{
    /**
     * Liste des méthodes HTTP supportées.
     */
    private const array VALID_METHODS = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'];

    /**
     * @var string Le chemin (URI) de la route.
     */
    private string $path;

    /**
     * @var string Le nom unique de la route.
     */
    private string $name;

    /**
     * @var array<string> Les méthodes HTTP autorisées pour cette route.
     */
    private array $methods;

    /**
     * @var array L'action à exécuter (généralement [Controller::class, 'method']).
     */
    private array $action = [];

    /**
     * Constructeur de l'attribut Route.
     *
     * @param string          $path    Le chemin de l'URL (ex: "/produits").
     * @param string          $name    Le nom de la route (optionnel).
     * @param string|string[] $methods La ou les méthodes HTTP acceptées (défaut: "GET").
     *
     * @throws InvalidArgumentException Si une méthode HTTP fournie n'est pas valide.
     */
    public function __construct(string $path, string $name = '', string|array $methods = 'GET')
    {
        $this->setPath($path);
        $this->setName($name);
        $this->setMethods($methods);
    }

    /**
     * Retourne le pattern regex correspondant au chemin.
     * Remplace {param} par ([^/]+).
     *
     * @return string
     */
    public function getRegex(): string
    {
        $path = $this->getPath();
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $regex . '$#';
    }

    /**
     * Récupère le nom de la route.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Définit le nom de la route.
     *
     * @param string $name Le nom de la route.
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Définit le chemin de la route avec normalisation.
     * Assure qu'il commence par "/" et ne finit pas par "/" (sauf si racine).
     *
     * @param string $path
     * @return void
     */
    public function setPath(string $path): void
    {
        // Supprime les espaces et les slashs de début/fin pour normaliser
        $path = trim($path);

        // Si le chemin est vide ou juste "/", on garde "/"
        if ($path === '' || $path === '/') {
            $this->path = '/';
            return;
        }

        // Assure le slash au début et supprime celui de la fin
        $this->path = '/' . trim($path, '/');
    }

    /**
     * Récupère le chemin de la route.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Définit et valide les méthodes HTTP.
     *
     * @param string|array $methods
     * @return void
     * @throws InvalidArgumentException
     */
    private function setMethods(string|array $methods): void
    {
        $methodsArray = is_string($methods) ? [$methods] : $methods;
        $normalizedMethods = [];

        foreach ($methodsArray as $method) {
            $upperMethod = strtoupper($method);
            if (!in_array($upperMethod, self::VALID_METHODS, true)) {
                throw new InvalidArgumentException(sprintf('Invalid HTTP method: "%s". Allowed: %s', $method, implode(', ', self::VALID_METHODS)));
            }
            $normalizedMethods[] = $upperMethod;
        }

        // Élimine les doublons et réindexe
        $this->methods = array_values(array_unique($normalizedMethods));
    }

    /**
     * Récupère les méthodes HTTP autorisées.
     *
     * @return array<string>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Récupère l'action associée.
     *
     * @return array
     */
    public function getAction(): array
    {
        return $this->action;
    }

    /**
     * Définit l'action associée (Contrôleur et méthode).
     *
     * @param array $action
     * @return void
     */
    public function setAction(array $action): void
    {
        $this->action = $action;
    }
}
