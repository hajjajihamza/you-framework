<?php

namespace YouMake\Command\Generator;

use YouConsole\Command\AbstractCommand;
use YouConsole\Input\Input;
use YouConsole\Output\Output;
use RuntimeException;
use YouKernel\Component\Container\Container;

/**
 * Classe de base pour les commandes de génération.
 * Fournit des méthodes utilitaires pour charger des stubs,
 * remplacer des placeholders et écrire des fichiers.
 *
 * @package YouMake\Command\Generator
 */
abstract class AbstractGeneratorCommand extends AbstractCommand
{
    public function __construct(protected Container $container)
    {
        parent::__construct();
    }

    /**
     * Retourne le chemin vers le stub à utiliser.
     *
     * @return string
     */
    abstract protected function getStubPath(): string;

    /**
     * Retourne le chemin de destination du fichier généré.
     *
     * @param string $className Le nom de la classe
     * @return string
     */
    abstract protected function getDestinationPath(string $className): string;

    /**
     * Retourne les remplacements à effectuer dans le stub.
     *
     * @param string $className Le nom de la classe
     * @return array<string, string>
     */
    protected function getReplacements(string $className): array
    {
        return [
            '{{ namespace }}' => $this->getDefaultNamespace($className),
            '{{ class }}' => $this->getClassName($className),
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
        $className = $input->getArgument('name');

        if (!$className) {
            $output->error("Le nom est obligatoire.");
            return self::STATUS_ERROR;
        }

        $path = $this->getDestinationPath($className);

        if (file_exists($path)) {
            $output->error("Le fichier existe déjà : $path");
            return self::STATUS_ERROR;
        }

        $this->makeDirectory($path);

        $content = $this->buildClass($className);

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
     * @param string $className
     * @return string
     */
    protected function buildClass(string $className): string
    {
        $stub = file_get_contents($this->getStubPath());

        if ($stub === false) {
            throw new RuntimeException("Stub introuvable : " . $this->getStubPath());
        }

        $replacements = $this->getReplacements($className);

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
            if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
            }
        }
    }

    /**
     * Retourne le namespace par défaut.
     *
     * @param string $className
     * @return string
     */
    protected function getDefaultNamespace(string $className): string
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
