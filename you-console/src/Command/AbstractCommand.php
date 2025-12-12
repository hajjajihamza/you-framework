<?php

namespace YouConsole\Command;

use YouConsole\Input\ArgumentDefinition;
use YouConsole\Input\Input;
use YouConsole\Input\OptionDefinition;
use YouConsole\Output\Output;

/**
 * Classe abstraite de base pour toutes les commandes CLI.
 *
 * Toutes les commandes doivent étendre cette classe et implémenter
 * les méthodes configure() et execute().
 *
 * @package YouConsole\Command
 */
abstract class AbstractCommand
{
    /** Status de retour de la commande pour succès */
    public const int STATUS_SUCCESS = 0;

    /** Status de retour de la commande pour erreur */
    public const int STATUS_ERROR = 1;

    /** Status de retour de la commande pour avertissement */
    public const int STATUS_WARNING = 2;

    /** Status de retour de la commande pour information */
    public const int STATUS_INFO = 3;
    
    /** @var string Nom de la commande */
    protected string $name = '';

    /** @var string Description de la commande */
    protected string $description = '';

    /** @var array<ArgumentDefinition> Définitions des arguments */
    protected array $arguments = [];

    /** @var array<OptionDefinition> Définitions des options */
    protected array $options = [];

    /**
     * Constructeur de la commande.
     * Appelle automatiquement configure() pour initialiser la commande.
     */
    public function __construct()
    {
        $this->configure();
    }

    /**
     * Configure la commande (nom, description, arguments, options).
     *
     * Cette méthode doit être implémentée par chaque commande pour définir
     * ses propriétés et ses paramètres.
     */
    abstract protected function configure(): void;

    /**
     * Exécute la logique de la commande.
     *
     * @param Input $input Entrées de la commande
     * @param Output $output Sortie pour afficher des messages
     * @return int Code de retour (0 = succès, autre = erreur)
     */
    abstract protected function execute(Input $input, Output $output): int;

    /**
     * Point d'entrée pour exécuter la commande.
     *
     * @param Input $input Entrées de la commande
     * @param Output $output Sortie pour afficher des messages
     * @return int Code de retour
     */
    public function run(Input $input, Output $output): int
    {
        return $this->execute($input, $output);
    }

    /**
     * Définit le nom de la commande.
     *
     * @param string $name Nom de la commande
     * @return static
     */
    protected function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Définit la description de la commande.
     *
     * @param string $description Description de la commande
     * @return static
     */
    protected function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Ajoute un argument à la commande.
     *
     * @param string $name Nom de l'argument
     * @param bool $required Indique si l'argument est obligatoire
     * @param string $description Description de l'argument
     * @return static
     */
    protected function addArgument(string $name, bool $required = false, string $description = ''): static
    {
        $this->arguments[] = new ArgumentDefinition($name, $required, $description);
        return $this;
    }

    /**
     * Ajoute une option à la commande.
     *
     * @param string $name Nom de l'option
     * @param string|null $shortcut Raccourci de l'option (une lettre)
     * @param bool $requiresValue Indique si l'option nécessite une valeur
     * @param string $description Description de l'option
     * @return static
     */
    protected function addOption(
        string $name,
        ?string $shortcut = null,
        bool $requiresValue = false,
        string $description = ''
    ): static {
        $this->options[] = new OptionDefinition($name, $shortcut, $requiresValue, $description);
        return $this;
    }

    /**
     * Récupère le nom de la commande.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Récupère la description de la commande.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Récupère les définitions des arguments.
     *
     * @return array<ArgumentDefinition>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Récupère les définitions des options.
     *
     * @return array<OptionDefinition>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}