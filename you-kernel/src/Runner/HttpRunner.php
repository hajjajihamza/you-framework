<?php

namespace YouKernel\Runner;

use JetBrains\PhpStorm\NoReturn;
use YouHttpFoundation\Request;
use YouKernel\Component\Http\HttpKernel;

/**
 * Classe HttpRunner
 *
 * Responsable de l'exécution du cycle de vie HTTP.
 * Elle transforme l'environnement PHP en objet Request,
 * délègue le traitement au kernel HTTP et envoie la réponse.
 *
 * @package YouKernel\Runner
 * @author  Hamza Hajjaji <https://github.com/hajjvero>
 */
final class HttpRunner
{
    /**
     * Exécute une requête HTTP complète.
     *
     * @param HttpKernel $kernel Kernel HTTP initialisé
     *
     * @return void
     */
    #[NoReturn]
    public function run(HttpKernel $kernel): void
    {
        $response = $kernel->handle();
        $response->send();
    }
}