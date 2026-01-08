<?php

if (!function_exists('base_path')) {
    /**
     * Récupère le chemin absolu vers la racine du projet.
     *
     * @param string $path Un chemin relatif à ajouter à la racine.
     * @return string Le chemin absolu complet.
     */
    function base_path(string $path = ''): string
    {
        $base = dirname(__DIR__);
        return $path ? $base . DIRECTORY_SEPARATOR . $path : $base;
    }
}
