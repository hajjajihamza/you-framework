<?php

namespace YouMake\Command\Generator;

use YouConfig\Config;
use YouConsole\Input\Input;
use YouConsole\Output\Output;

/**
 * Commande pour générer un modèle ORM.
 */
class EntityMakeCommand extends AbstractGeneratorCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:entity')
            ->setDescription('Génère un nouveau modèle ORM')
            ->addArgument('name', true, 'Le nom du modèle')
            ->addOption('no-repository', null, false, 'Génère une entité sans repository');
    }

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../Resources/stubs/entity.stub';
    }

    /**
     * @return string
     */
    protected function getRepositoryStubPath(): string
    {
        return __DIR__ . '/../../Resources/stubs/repository.stub';
    }

    /**
     * @param string $className
     * @return string
     * @throws \ReflectionException
     */
    protected function getDestinationPath(string $className): string
    {
        $config = $this->container->get(Config::class);
        $projectDir = $this->container->get('project_dir');

        $entitiesPath = $projectDir . '/' . ltrim($config->get('database.entities_path', 'src/Entity'), '/');
        $className = str_replace('\\', '/', $className);

        return sprintf('%s/%s.php', $entitiesPath, $className);
    }

    /**
     * @param string $className
     * @return string
     * @throws \ReflectionException
     */
    protected function getDestinationPathRepository(string $className): string
    {
        $config = $this->container->get(Config::class);
        $projectDir = $this->container->get('project_dir');

        $repositoriesPath = $projectDir . '/' . ltrim($config->get('database.repositories_path', 'src/Repository'), '/');
        $repositoryName = $this->getRepositoryName($className);

        return sprintf('%s/%s.php', $repositoriesPath, $repositoryName);
    }

    /**
     * @param string $className
     * @return array<string, string>
     */
    protected function getReplacements(string $className): array
    {
        $replacements = parent::getReplacements($className);
        $replacements['{{ table }}'] = $this->getTableName($className);

        return $replacements;
    }

    /**
     * @param string $className
     * @return array<string, string>
     */
    protected function getRepositoryReplacements(string $className): array
    {
        return [
            '{{ namespace }}' => $this->getDefaultRepositoryNamespace($className),
            '{{ class }}' => $this->getClassName($this->getRepositoryName($className)),
            '{{ entity_class }}' => $this->getClassName($className),
            '{{ entity_namespace }}' => $this->getDefaultNamespace($className),
        ];
    }

    /**
     * @param Input $input
     * @param Output $output
     * @return int
     * @throws \ReflectionException
     */
    protected function execute(Input $input, Output $output): int
    {
        $className = $input->getArgument('name');

        // Générer l'entité via la classe parente
        $status = parent::execute($input, $output);

        if ($status !== self::STATUS_SUCCESS) {
            return $status;
        }

        // Générer le repository si l'option no-repository n'est pas présente
        if (!$input->getOption('no-repository')) {
            $repositoryPath = $this->getDestinationPathRepository($className);

            if (file_exists($repositoryPath)) {
                $output->comment("Le repository existe déjà : $repositoryPath");
                return self::STATUS_SUCCESS;
            }

            $this->makeDirectory($repositoryPath);

            $stubPath = $this->getRepositoryStubPath();
            $stub = file_get_contents($stubPath);

            if ($stub === false) {
                $output->error("Repository stub introuvable : $stubPath");
                return self::STATUS_ERROR;
            }

            $replacements = $this->getRepositoryReplacements($className);
            $content = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $stub
            );

            if (file_put_contents($repositoryPath, $content) === false) {
                $output->error("Impossible d'écrire le repository : $repositoryPath");
                return self::STATUS_ERROR;
            }

            $output->success("Repository généré avec succès : $repositoryPath");
        }

        return self::STATUS_SUCCESS;
    }

    /**
     * @param string $className
     * @return string
     */
    protected function getTableName(string $className): string
    {
        $class = $this->getClassName($className);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class));
    }

    /**
     * @param string $className
     * @return string
     */
    protected function getRepositoryName(string $className): string
    {
        return str_replace('\\', '/', $className) . 'Repository';
    }

    /**
     * @param string $className
     * @return string
     */
    protected function getDefaultNamespace(string $className): string
    {
        $namespace = 'App\\Entity';
        $parts = explode('\\', str_replace('/', '\\', $className));
        array_pop($parts);

        if (!empty($parts)) {
            $namespace .= '\\' . implode('\\', $parts);
        }

        return $namespace;
    }

    /**
     * @param string $className
     * @return string
     */
    protected function getDefaultRepositoryNamespace(string $className): string
    {
        $namespace = 'App\\Repository';
        $parts = explode('\\', str_replace('/', '\\', $className));
        array_pop($parts);

        if (!empty($parts)) {
            $namespace .= '\\' . implode('\\', $parts);
        }

        return $namespace;
    }
}
