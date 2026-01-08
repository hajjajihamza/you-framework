<?php

if (!function_exists('session')) {
    /**
     * Récupère une valeur de session ou l'instance de session.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function session(?string $key = null, mixed $default = null): mixed
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($key === null) {
            return $_SESSION;
        }

        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('with_errors')) {
    /**
     * Flashe des erreurs dans la session.
     *
     * @param array<string, string> $errors
     * @return void
     */
    function with_errors(array $errors): void
    {
        flash('_errors', $errors);
    }
}

if (!function_exists('errors')) {
    /**
     * Récupère les erreurs flashées.
     *
     * @param string|null $key
     * @return mixed
     */
    function errors(?string $key = null): mixed
    {
        $errors = flash('_errors') ?? [];

        if ($key) {
            return $errors[$key] ?? null;
        }

        return $errors;
    }
}

if (!function_exists('clear_errors')) {
    /**
     * Supprime les erreurs flashées.
     *
     * @return void
     */
    function clear_errors(): void
    {
        flash('_errors', []);
    }
}

if (!function_exists('with_old')) {
    /**
     * Flashe les anciennes entrées dans la session.
     *
     * @param array<string, string> $input
     * @return void
     */
    function with_old(array $input): void
    {
        flash('_old_input', $input);
    }
}

if (!function_exists('old')) {
    /**
     * Récupère une ancienne entrée flashée.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function old(string $key, mixed $default = null): mixed
    {
        // On ne supprime pas immédiatement old input car il peut être utilisé plusieurs fois dans la vue
        // Idéalement, le framework devrait nettoyer le flash 'old' à la fin de la requête suivante.
        // Pour faire simple ici, on regarde dans $_SESSION['_flash'] sans le consommer via flash(),
        // ou alors on le "re-flash" si on veut le garder pour toute la durée du rendu de la vue,
        // mais flash() le supprime au get.
        // Alternative : Regarder sans supprimer.
        // Utilisation de la fonction helper session() pour accéder aux données flashées sans les consommer
        $old = session('_flash')['_old_input'] ?? [];
        return $old[$key] ?? $default;
    }
}

if (!function_exists('clear_old')) {
    /**
     * Supprime les anciennes entrées flashées.
     *
     * @return void
     */
    function clear_old(): void
    {
        flash('_old_input', []);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Génère ou récupère le token CSRF.
     *
     * @return string
     */
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Génère un champ input hidden pour le token CSRF.
     *
     * @return string
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}



