<?php

namespace YouMake\Command\Generator;

/**
 * Commande pour générer un modèle ORM.
 */
class ModelMakeCommand extends AbstractGeneratorCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:model')
            ->setDescription('Génère un nouveau modèle ORM')
            ->addArgument('name', true, 'Le nom du modèle');
    }

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../Resources/stubs/model.stub';
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getDestinationPath(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        return getcwd() . '/src/Model/' . $name . '.php';
    }

    /**
     * @param string $name
     * @return array<string, string>
     */
    protected function getReplacements(string $name): array
    {
        $replacements = parent::getReplacements($name);
        $replacements['{{ table }}'] = $this->getTableName($name);

        return $replacements;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getTableName(string $name): string
    {
        $class = $this->getClassName($name);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . 's';
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getDefaultNamespace(string $name): string
    {
        $namespace = 'App\\Model';
        $parts = explode('\\', str_replace('/', '\\', $name));
        array_pop($parts);

        if (!empty($parts)) {
            $namespace .= '\\' . implode('\\', $parts);
        }

        return $namespace;
    }
}
