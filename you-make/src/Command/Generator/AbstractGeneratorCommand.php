<?php

namespace YouMake\Command\Generator;

use YouConsole\Command\AbstractCommand;
use YouConsole\Input\Input;
use YouConsole\Output\Output;
use RuntimeException;

/**
 * Classe de base pour les commandes de génération.
 * Fournit des méthodes utilitaires pour charger des stubs,
 * remplacer des placeholders et écrire des fichiers.
 *
 * @package YouMake\Command\Generator
 */
abstract class AbstractGeneratorCommand extends AbstractCommand
{
    /**
     * Retourne le chemin vers le stub à utiliser.
     *
     * @return string
     */
    abstract protected function getStubPath(): string;

    /**
     * Retourne le chemin de destination du fichier généré.
     *
     * @param string $name Nom de l'élément à générer
     * @return string
     */
    abstract protected function getDestinationPath(string $name): string;

    /**
     * Retourne les remplacements à effectuer dans le stub.
     *
     * @param string $name Nom de l'élément à générer
     * @return array<string, string>
     */
    protected function getReplacements(string $name): array
    {
        return [
            '{{ name }}' => $name,
            '{{ namespace }}' => $this->getDefaultNamespace($name),
            '{{ class }}' => $this->getClassName($name),
        ];
    }

    /**
     * Exécute la logique de génération.
     *
     * @param Input $input
     * @param Output $output
     * @return int
     */
    protected function execute(Input $input, Output $output): int
    {
        $name = $input->getArgument('name');

        if (!$name) {
            $output->error("Le nom est obligatoire.");
            return self::STATUS_ERROR;
        }

        $path = $this->getDestinationPath($name);

        if (file_exists($path)) {
            $output->error("Le fichier existe déjà : $path");
            return self::STATUS_ERROR;
        }

        $this->makeDirectory($path);

        $content = $this->buildClass($name);

        if (file_put_contents($path, $content) === false) {
            $output->error("Impossible d'écrire le fichier : $path");
            return self::STATUS_ERROR;
        }

        $output->success("Généré avec succès : $path");

        return self::STATUS_SUCCESS;
    }

    /**
     * Construit le contenu de la classe à partir du stub.
     *
     * @param string $name
     * @return string
     */
    protected function buildClass(string $name): string
    {
        $stub = file_get_contents($this->getStubPath());

        if ($stub === false) {
            throw new RuntimeException("Stub introuvable : " . $this->getStubPath());
        }

        $replacements = $this->getReplacements($name);

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $stub
        );
    }

    /**
     * Crée le répertoire si nécessaire.
     *
     * @param string $path
     * @return void
     */
    protected function makeDirectory(string $path): void
    {
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    /**
     * Retourne le namespace par défaut.
     *
     * @param string $name
     * @return string
     */
    protected function getDefaultNamespace(string $name): string
    {
        return 'App';
    }

    /**
     * Retourne le nom de la classe.
     *
     * @param string $name
     * @return string
     */
    protected function getClassName(string $name): string
    {
        return basename(str_replace('\\', '/', $name));
    }
}
