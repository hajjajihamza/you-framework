<?php

namespace YouHttpFoundation;

/**
 * Request représente une requête HTTP.
 *
 * Elle encapsule les variables globales PHP ($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER).
 */
class Request
{
    /**
     * Paramètres de requête ($_GET).
     * @var ParameterBag
     */
    public ParameterBag $query;

    /**
     * Paramètres de requête ($_POST).
     * @var ParameterBag
     */
    public ParameterBag $request;

    /**
     * Cookies ($_COOKIE).
     * @var ParameterBag
     */
    public ParameterBag $cookies;

    /**
     * Fichiers téléchargés ($_FILES).
     * @var ParameterBag
     */
    public ParameterBag $files;

    /**
     * Variables serveur et d'exécution ($_SERVER).
     * @var ParameterBag
     */
    public ParameterBag $server;

    /**
     * Constructeur.
     *
     * @param array                $query      Paramètres de requête GET
     * @param array                $request    Paramètres de requête POST
     * @param array                $cookies    Cookies
     * @param array                $files      Fichiers
     * @param array                $server     Variables serveur
     */
    public function __construct(array $query = [], array $request = [], array $cookies = [], array $files = [], array $server = [])
    {
        $this->initialize($query, $request, $cookies, $files, $server);
    }

    /**
     * Initialise la requête avec les données fournies.
     */
    public function initialize(array $query = [], array $request = [], array $cookies = [], array $files = [], array $server = []): void
    {
        $this->query = new ParameterBag($query);
        $this->request = new ParameterBag($request);
        $this->cookies = new ParameterBag($cookies);
        $this->files = new ParameterBag($files);
        $this->server = new ParameterBag($server);
    }

    /**
     * Crée une instance de Request à partir des variables globales de PHP.
     *
     * @return static
     * @throws \JsonException
     */
    public static function createFromGlobals(): static
    {
        $request = new static($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);

        // Si la requête est POST, PUT ou PATCH, on parse le corps de la requête en JSON
        if (($request->isMethod('POST')  || $request->isMethod('PUT') || $request->isMethod('PATCH')) && $request->server->get('CONTENT_TYPE', '') === 'application/json') {
            $request->request->replace(json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR));
        }

        return $request;
    }

    /**
     * Récupère le chemin de la requête (path info).
     *
     * @return string
     */
    public function getPath(): string
    {
        // On enlève le slash final si présent
        $path = rtrim(parse_url($this->server->get('REQUEST_URI'), PHP_URL_PATH), '/');

        return empty($path) ? '/' : $path; // Si le path est vide, on retourne '/'
    }


    /**
     * Récupère la méthode HTTP.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
    }

    /**
     * Vérifie si la méthode HTTP de la requête correspond à la méthode donnée.
     *
     * @param string $method La méthode HTTP à vérifier (ex: 'POST', 'GET').
     *
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->getMethod();
    }
}
