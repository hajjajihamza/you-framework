<?php

namespace YouConsole\Command;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;
use YouKernel\Component\Container\Container;

/**
 * Classe responsable de la découverte automatique des commandes.
 *
 * Scanne un répertoire pour trouver toutes les classes qui étendent Command
 * et les instancie automatiquement.
 *
 * @package YouConsole\Command
 */
readonly class CommandDiscovery
{
    public function __construct(private Container $container)
    {
    }

    /**
     * Découvre automatiquement les commandes dans un répertoire.
     *
     * @param string $directory Répertoire à scanner
     * @return array<AbstractCommand> Liste des commandes découvertes
     */
    public function discover(string $directory): array
    {
        $commands = [];

        foreach (discover_classes($directory) as $className) {
            $reflection = new ReflectionClass($className);

            // Ignorer les classes abstraites et celles qui n'étendent pas Command
            if ($reflection->isAbstract() || !$reflection->isSubclassOf(AbstractCommand::class)) {
                continue;
            }

            // Récupérer l'instance de la commande via le container pour bénéficier des services (Dependency Injection)
            $command = $this->container->get($className);
            $commands[] = $command;
        }

        return $commands;
    }
}