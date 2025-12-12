<?php

declare(strict_types=1);

namespace YouConsole\Input;

/**
 * Définition d'une option de commande CLI.
 *
 * Représente une option (--option ou -o) d'une commande avec ses propriétés
 * (nom, raccourci, valeur requise, description).
 *
 * @package YouCli\Input
 */
readonly class OptionDefinition
{
    /**
     * @param string $name Le nom de l'option (sans les tirets)
     * @param string|null $shortcut Le raccourci court de l'option (une seule lettre, sans tiret)
     * @param bool $requiresValue Indique si l'option nécessite une valeur
     * @param string $description Description de l'option pour l'aide
     */
    public function __construct(
        private string  $name,
        private ?string $shortcut = null,
        private bool    $requiresValue = false,
        private string  $description = ''
    ) {
    }

    /**
     * Récupère le nom de l'option.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Récupère le raccourci de l'option.
     *
     * @return string|null
     */
    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    /**
     * Vérifie si l'option nécessite une valeur.
     *
     * @return bool
     */
    public function requiresValue(): bool
    {
        return $this->requiresValue;
    }

    /**
     * Récupère la description de l'option.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
