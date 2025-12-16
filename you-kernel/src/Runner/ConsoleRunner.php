<?php

namespace YouKernel\Runner;

use YouConsole\YouConsoleKernel;
use JetBrains\PhpStorm\NoReturn;

/**
 * Classe ConsoleRunner
 *
 * Responsable de l'exécution des commandes CLI.
 * Elle délègue le traitement au kernel console
 * et termine le script avec le code de retour approprié.
 *
 * @package YouKernel\Runner
 * @author  Hamza Hajjaji <https://github.com/hajjvero>
 */
final class ConsoleRunner
{
    /**
     * Lance l'exécution du kernel Console.
     *
     * @param YouConsoleKernel $kernel Kernel console
     * @param array            $argv   Arguments CLI
     *
     * @return void Ne retourne jamais (exit)
     */
    #[NoReturn]
    public function run(YouConsoleKernel $kernel, array $argv): void
    {
        exit($kernel->run($argv));
    }
}