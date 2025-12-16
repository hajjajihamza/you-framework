<?php

namespace YouKernel\Bootstrap;

use RuntimeException;

/**
 * Classe ProjectResolver
 *
 * Responsable de la résolution du répertoire racine du projet.
 * Cette classe isole la logique de détection du project directory
 * afin de garantir une initialisation cohérente du framework,
 * aussi bien en mode HTTP qu'en mode CLI.
 *
 * Elle constitue une étape clé du bootstrap du noyau (Kernel).
 *
 * @package YouKernel\Bootstrap
 * @author Hamza Hajjaji <https://github.com/hajjvero>
 */
final class ProjectResolver
{
    /**
     * Résout le chemin absolu du répertoire racine du projet.
     *
     * Stratégies :
     * - Utilise le chemin fourni manuellement
     * - Détecte automatiquement via SCRIPT_FILENAME (HTTP)
     * - Utilise getcwd() en mode CLI
     *
     * @param string|null $projectDir Chemin du projet fourni manuellement
     *
     * @return string Chemin absolu normalisé du projet
     *
     * @throws RuntimeException Si le chemin ne peut pas être déterminé
     */
    public function resolve(?string $projectDir): string
    {
        if ($projectDir !== null) {
            return rtrim($projectDir, '/');
        }

        if (isset($_SERVER['SCRIPT_FILENAME'])) {
            return dirname($_SERVER['SCRIPT_FILENAME'], 2);
        }

        return getcwd() ?: throw new RuntimeException(
            'Impossible de déterminer le répertoire du projet'
        );
    }
}