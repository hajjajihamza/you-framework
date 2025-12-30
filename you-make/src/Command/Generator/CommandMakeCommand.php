<?php

namespace YouMake\Command\Generator;

use YouConsole\Input\Input;
use YouConsole\Output\Output;

/**
 * Commande pour générer une nouvelle commande console.
 */
class CommandMakeCommand extends AbstractGeneratorCommand
{
    private ?Input $currentInput = null;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:command')
            ->setDescription('Génère une nouvelle commande console')
            ->addArgument('name', true, 'Le nom de la classe de la commande')
            ->addArgument('command_name', false, 'Le nom de la commande (ex: app:my-command)');
    }

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../Resources/stubs/command.stub';
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getDestinationPath(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        return getcwd() . '/src/Command/' . $name . '.php';
    }

    /**
     * @param Input $input
     * @param Output $output
     * @return int
     */
    protected function execute(Input $input, Output $output): int
    {
        $this->currentInput = $input;
        return parent::execute($input, $output);
    }

    /**
     * @param string $name
     * @return array<string, string>
     */
    protected function getReplacements(string $name): array
    {
        $replacements = parent::getReplacements($name);
        $commandName = $this->currentInput?->getArgument('command_name');

        if (!$commandName) {
            $class = $this->getClassName($name);
            $commandName = 'app:' . strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', str_replace('Command', '', $class)));
        }

        $replacements['{{ command_name }}'] = $commandName;

        return $replacements;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getDefaultNamespace(string $name): string
    {
        $namespace = 'App\\Command';
        $parts = explode('\\', str_replace('/', '\\', $name));
        array_pop($parts);

        if (!empty($parts)) {
            $namespace .= '\\' . implode('\\', $parts);
        }

        return $namespace;
    }
}
