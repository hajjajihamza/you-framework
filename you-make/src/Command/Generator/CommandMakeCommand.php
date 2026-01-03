<?php

namespace YouMake\Command\Generator;

use ReflectionException;
use YouConfig\Config;

/**
 * Commande pour générer une nouvelle commande console.
 */
class CommandMakeCommand extends AbstractGeneratorCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:command')
            ->setDescription('Génère une nouvelle commande console')
            ->addArgument('name', true, 'Le nom de la classe de la commande');
    }

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../Resources/stubs/command.stub';
    }

    /**
     * {@inheritDoc}
     * @param string $className
     * @return string
     * @throws ReflectionException
     */
    protected function getDestinationPath(string $className): string
    {
        $config = $this->container->get(Config::class);
        $projectDir = $this->container->get('project_dir');

        $commandsPath = $projectDir . '/' . ltrim($config->get('app.commands.resource', 'src/Command'), '/');
        $className = str_replace('\\', '/', $className);

        return sprintf('%s/%s.php', $commandsPath, ltrim($className, '/'));
    }

    /**
     * {@inheritDoc}
     */
    protected function getReplacements(string $className): array
    {
        $replacements = parent::getReplacements($className);

        $class = $this->getClassName($className);
        $commandName = 'app:' . strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', str_replace('Command', '', $class)));

        $replacements['{{ command_name }}'] = $commandName;

        return $replacements;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultNamespace(string $className): string
    {
        $namespace = 'App\\Command';
        $parts = explode('\\', str_replace('/', '\\', $className));
        array_pop($parts);

        if (!empty($parts)) {
            $namespace .= '\\' . implode('\\', $parts);
        }

        return $namespace;
    }
}
