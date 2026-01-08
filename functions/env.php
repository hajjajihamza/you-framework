<?php

if (!function_exists('env')) {
    /**
     * Récupère la valeur d'une variable d'environnement ou renvoie une valeur par défaut.
     *
     * @param string $key La clé de la variable d'environnement.
     * @param mixed $default La valeur par défaut à retourner si la clé n'existe pas.
     * @return mixed La valeur de la variable d'environnement ou la valeur par défaut.
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key) ?: $_ENV[$key] ?? $default;

        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        if ($value === 'null') {
            return null;
        }

        return $value;
    }
}
