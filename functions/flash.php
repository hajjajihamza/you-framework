<?php

if (!function_exists('flash')) {
    /**
     * Définit ou récupère un message flash.
     *
     * @param string $key La clé du message flash.
     * @param mixed $value La valeur du message (optionnel).
     * @return mixed
     */
    function flash(string $key, mixed $value = null): mixed
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($value) {
            $_SESSION['_flash'][$key] = $value;
            return $value;
        }

        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }
}
