<?php

namespace YouMake\Command\Generator;

/**
 * Commande pour générer un contrôleur.
 */
class ControllerMakeCommand extends AbstractGeneratorCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:controller')
            ->setDescription('Génère un nouveau contrôleur')
            ->addArgument('name', true, 'Le nom du contrôleur');
    }

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../Resources/stubs/controller.stub';
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getDestinationPath(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        return getcwd() . '/src/Controller/' . $name . '.php';
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getDefaultNamespace(string $name): string
    {
        $namespace = 'App\\Controller';
        $parts = explode('\\', str_replace('/', '\\', $name));
        array_pop($parts);

        if (!empty($parts)) {
            $namespace .= '\\' . implode('\\', $parts);
        }

        return $namespace;
    }
}
