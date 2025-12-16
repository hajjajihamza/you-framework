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

        if (!is_dir($directory)) {
            return $commands;
        }

        /** @var SplFileInfo[] $iterator */
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->getFQCN($file->getPathname());

            try {
                // Vérifier que la classe existe et peut être chargée
                if (!class_exists($className)) {
                    continue;
                }

                $reflection = new ReflectionClass($className);

                // Ignorer les classes abstraites et celles qui n'étendent pas Command
                if ($reflection->isAbstract() || !$reflection->isSubclassOf(AbstractCommand::class)) {
                    continue;
                }

                // Récupérer l'instance de la commande via le container pour bénéficier des services (Dependency Injection)
                $command = $this->container->get($className);
                $commands[] = $command;

            } catch (\Throwable $e) {
                // Ignorer les erreurs de chargement de classe
                continue;
            }
        }

        return $commands;
    }

    /**
     * Extracts the fully qualified class name from a PHP file.
     *
     * @param string $filePath
     * @return string|null The FQCN or null if not found.
     */
    private function getFQCN(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        // Match namespace
        if (!preg_match('/^\s*namespace\s+([a-zA-Z0-9_\\\\]+)\s*;/m', $content, $namespaceMatches)) {
            return null;
        }

        // Match class name (supports abstract, final, readonly)
        // Improved regex to handle modifiers before 'class'
        if (!preg_match('/^\s*(?:abstract\s+|final\s+|readonly\s+)*class\s+(\w+)/m', $content, $classMatches)) {
            return null;
        }

        return $namespaceMatches[1] . '\\' . $classMatches[1];
    }
}