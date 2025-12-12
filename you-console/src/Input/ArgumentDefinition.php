<?php

declare(strict_types=1);

namespace YouConsole\Input;

/**
 * Définition d'un argument de commande CLI.
 *
 * Représente un argument positionnel d'une commande avec ses propriétés
 * (nom, caractère requis, description).
 *
 * @package YouCli\Input
 */
readonly class ArgumentDefinition
{
    /**
     * @param string $name Le nom de l'argument
     * @param bool $required Indique si l'argument est obligatoire
     * @param string $description Description de l'argument pour l'aide
     */
    public function __construct(
        private string $name,
        private bool   $required = false,
        private string $description = ''
    ) {
    }

    /**
     * Récupère le nom de l'argument.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Vérifie si l'argument est requis.
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Récupère la description de l'argument.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
