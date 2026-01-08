<?php

if (!function_exists('redirect')) {
    /**
     * Redirige vers une URL donnée.
     *
     * @param string $url L'URL de destination.
     * @return void
     */
    function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
}

if (!function_exists('back')) {
    /**
     * Redirige vers la page précédente.
     *
     * @return void
     */
    function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        redirect($referer);
    }
}

if (!function_exists('abort')) {
    /**
     * Interrompt la requête avec un code d'erreur HTTP.
     *
     * @param int $code Le code de statut HTTP.
     * @param string $message Un message d'erreur optionnel.
     * @return void
     */
    function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        if ($message) {
            echo $message;
        }
        exit;
    }
}

if (!function_exists('request_method')) {
    /**
     * Retourne la méthode de la requête HTTP.
     *
     * @return string
     */
    function request_method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if ($method === 'POST' && isset($_POST['_method'])) {
            return strtoupper($_POST['_method']);
        }

        return $method;
    }
}

if (!function_exists('get_host')) {
    function get_host(): string
    {
        $isHttps =
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === 443);

        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $scheme . '://' . $host;
    }
}

if (!function_exists('request_path')) {
    /**
     * Retourne le chemin de la requête actuelle.
     *
     * @return string
     */
    function request_path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return parse_url($uri, PHP_URL_PATH) ?: '/';
    }
}

if (!function_exists('is_method')) {
    /**
     * Vérifie si la méthode de la requête correspond à celle donnée.
     *
     * @param string $method
     * @return bool
     */
    function is_method(string $method): bool
    {
        return request_method() === strtoupper($method);
    }
}

if (!function_exists('is_post')) {
    /**
     * Vérifie si la requête est de type POST.
     *
     * @return bool
     */
    function is_post(): bool
    {
        return is_method('POST');
    }
}

if (!function_exists('is_get')) {
    /**
     * Vérifie si la requête est de type GET.
     *
     * @return bool
     */
    function is_get(): bool
    {
        return is_method('GET');
    }
}

if (!function_exists('method_field')) {
    /**
     * Génère un champ input hidden pour simuler une méthode HTTP (PUT, DELETE, etc.).
     *
     * @param string $method
     * @return string
     */
    function method_field(string $method): string
    {
        if (!in_array(strtoupper($method), ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'])) {
            throw new InvalidArgumentException("Invalid HTTP method: $method");
        }

        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
}