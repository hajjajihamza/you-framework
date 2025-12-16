<?php

namespace YouKernel\Bootstrap;

use Dotenv\Dotenv;

/**
 * Classe EnvironmentLoader
 *
 * Charge les variables d'environnement depuis le fichier .env
 * situé à la racine du projet.
 *
 * Cette classe encapsule l'utilisation de la bibliothèque Dotenv
 * et garantit une séparation claire entre configuration système
 * et logique applicative.
 *
 * @package YouKernel\Bootstrap
 * @author Hamza Hajjaji <https://github.com/hajjvero>
 */
final class EnvironmentLoader
{
    /**
     * Charge les variables d'environnement du projet.
     *
     * @param string $projectDir Chemin absolu du projet
     *
     * @return void
     */
    public function load(string $projectDir): void
    {
        Dotenv::createImmutable($projectDir)->load();
    }
}