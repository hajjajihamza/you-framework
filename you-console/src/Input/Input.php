<?php

declare(strict_types=1);

namespace YouConsole\Input;

/**
 * Classe de gestion des entrées de la ligne de commande.
 *
 * Parse et stocke les arguments et options fournis lors de l'exécution
 * d'une commande CLI. Supporte plusieurs formats d'options :
 * - --option=value
 * - --option value
 * - -o value
 * - --flag (option booléenne)
 *
 * @package YouConsole\Input
 */
class Input
{
    /** @var array<string, mixed> Arguments indexés par nom */
    private array $arguments = [];

    /** @var array<string, mixed> Options indexées par nom */
    private array $options = [];

    /** @var string Nom de la commande exécutée */
    private string $command = '';

    /**
     * Crée une instance Input à partir des arguments bruts.
     *
     * @param array<string> $argv Tableau des arguments de la ligne de commande
     * @param array<ArgumentDefinition> $argumentDefinitions Définitions des arguments attendus
     * @param array<OptionDefinition> $optionDefinitions Définitions des options attendues
     */
    public function __construct(
        array $argv,
        array $argumentDefinitions = [],
        array $optionDefinitions = []
    ) {
        $this->parse($argv, $argumentDefinitions, $optionDefinitions);
    }

    /**
     * Parse les arguments bruts de la ligne de commande.
     *
     * @param array<string> $argv Arguments bruts
     * @param array<ArgumentDefinition> $argumentDefinitions Définitions des arguments
     * @param array<OptionDefinition> $optionDefinitions Définitions des options
     */
    private function parse(array $argv, array $argumentDefinitions, array $optionDefinitions): void
    {
        // Le premier argument est le nom de la commande
        if (!empty($argv)) {
            $this->command = array_shift($argv);
        }

        $positionalArgs = [];
        $i = 0;

        while ($i < count($argv)) {
            $arg = $argv[$i];

            // Option longue : --option ou --option=value
            if (str_starts_with($arg, '--')) {
                $this->parseLongOption($arg, $argv, $i, $optionDefinitions);
            }
            // Option courte : -o
            elseif (str_starts_with($arg, '-') && strlen($arg) > 1) {
                $this->parseShortOption($arg, $argv, $i, $optionDefinitions);
            }
            // Argument positionnel
            else {
                $positionalArgs[] = $arg;
            }

            $i++;
        }

        // Mapper les arguments positionnels aux définitions
        foreach ($argumentDefinitions as $index => $definition) {
            if (isset($positionalArgs[$index])) {
                $this->arguments[$definition->getName()] = $positionalArgs[$index];
            }
        }
    }

    /**
     * Parse une option longue (--option).
     *
     * @param string $arg L'argument actuel
     * @param array<string> $argv Tous les arguments
     * @param int &$i Index actuel (passé par référence)
     * @param array<OptionDefinition> $optionDefinitions Définitions des options
     */
    private function parseLongOption(string $arg, array $argv, int &$i, array $optionDefinitions): void
    {
        $optionName = substr($arg, 2);
        $optionValue = true;

        // Format --option=value
        if (str_contains($optionName, '=')) {
            [$optionName, $optionValue] = explode('=', $optionName, 2);
        }
        // Format --option value (si l'option nécessite une valeur)
        else {
            $definition = $this->findOptionDefinition($optionName, $optionDefinitions);
            if ($definition && $definition->requiresValue() && isset($argv[$i + 1]) && !str_starts_with($argv[$i + 1], '-')) {
                $optionValue = $argv[$i + 1];
                $i++; // Sauter la valeur au prochain tour
            }
        }

        $this->options[$optionName] = $optionValue;
    }

    /**
     * Parse une courte option (-o).
     *
     * @param string $arg L'argument actuel
     * @param array<string> $argv Tous les arguments
     * @param int &$i Index actuel (passé par référence)
     * @param array<OptionDefinition> $optionDefinitions Définitions des options
     */
    private function parseShortOption(string $arg, array $argv, int &$i, array $optionDefinitions): void
    {
        $shortcut = substr($arg, 1);

        // Trouver l'option correspondante au raccourci
        $definition = $this->findOptionDefinitionByShortcut($shortcut, $optionDefinitions);

        if ($definition) {
            $optionName = $definition->getName();
            $optionValue = true;

            // Si l'option nécessite une valeur, prendre l'argument suivant
            if ($definition->requiresValue() && isset($argv[$i + 1]) && !str_starts_with($argv[$i + 1], '-')) {
                $optionValue = $argv[$i + 1];
                $i++; // Sauter la valeur au prochain tour
            }

            $this->options[$optionName] = $optionValue;
        }
    }

    /**
     * Trouve une définition d'option par son nom.
     *
     * @param string $name Nom de l'option
     * @param array<OptionDefinition> $optionDefinitions Définitions disponibles
     * @return OptionDefinition|null
     */
    private function findOptionDefinition(string $name, array $optionDefinitions): ?OptionDefinition
    {
        return array_find($optionDefinitions, static fn(OptionDefinition $definition) => $definition->getName() === $name);
    }

    /**
     * Trouve une définition d'option par son raccourci.
     *
     * @param string $shortcut Raccourci de l'option
     * @param array<OptionDefinition> $optionDefinitions Définitions disponibles
     * @return OptionDefinition|null
     */
    private function findOptionDefinitionByShortcut(string $shortcut, array $optionDefinitions): ?OptionDefinition
    {
        return array_find($optionDefinitions, static fn(OptionDefinition $definition) => $definition->getShortcut() === $shortcut);
    }

    /**
     * Récupère la valeur d'un argument.
     *
     * @param string $name Nom de l'argument
     * @param mixed $default Valeur par défaut si l'argument n'existe pas
     * @return mixed
     */
    public function getArgument(string $name, mixed $default = null): mixed
    {
        return $this->arguments[$name] ?? $default;
    }

    /**
     * Récupère la valeur d'une option.
     *
     * @param string $name Nom de l'option
     * @param mixed $default Valeur par défaut si l'option n'existe pas
     * @return mixed
     */
    public function getOption(string $name, mixed $default = null): mixed
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * Vérifie si une option est définie.
     *
     * @param string $name Nom de l'option
     * @return bool
     */
    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    /**
     * Vérifie si un argument est défini.
     *
     * @param string $name Nom de l'argument
     * @return bool
     */
    public function hasArgument(string $name): bool
    {
        return isset($this->arguments[$name]);
    }

    /**
     * Récupère le nom de la commande exécutée.
     *
     * @return string
     */
    public function getCommandName(): string
    {
        return $this->command;
    }
}
