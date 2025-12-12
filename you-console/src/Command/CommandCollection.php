<?php

namespace YouConsole\Command;

use InvalidArgumentException;

/**
 * Collection de commandes console.
 *
 * Cette classe gère une collection de commandes console, permettant d'ajouter,
 * de récupérer, de vérifier l'existence et de lister les commandes disponibles.
 * Les commandes sont indexées par leur nom unique.
 *
 * @package YouConsole\Command
 */
class CommandCollection
{
    /**
     * Tableau des commandes enregistrées.
     *
     * Les commandes sont stockées dans un tableau associatif où la clé est le nom
     * de la commande et la valeur est l'instance de la commande (AbstractCommand).
     *
     * @var array<string, AbstractCommand>
     */
    private array $commands = [];

    /**
     * Ajoute une commande à la collection.
     *
     * Enregistre une nouvelle commande dans la collection. Si une commande avec
     * le même nom existe déjà, elle sera écrasée par la nouvelle commande.
     *
     * @param AbstractCommand $command La commande à ajouter à la collection
     *
     * @return void
     *
     * @throws InvalidArgumentException Si le nom de la commande est vide
     */
    public function add(AbstractCommand $command): void
    {
        $name = $command->getName();

        if (empty($name)) {
            throw new InvalidArgumentException(
                'Le nom de la commande ne peut pas être vide.'
            );
        }

        $this->commands[$name] = $command;
    }

    /**
     * Récupère une commande par son nom.
     *
     * Retourne l'instance de la commande correspondant au nom fourni.
     * Si la commande n'existe pas, une exception est levée.
     *
     * @param string $name Le nom de la commande à récupérer
     *
     * @return AbstractCommand L'instance de la commande
     *
     * @throws InvalidArgumentException Si la commande n'existe pas dans la collection
     */
    public function get(string $name): AbstractCommand
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(
                sprintf('La commande "%s" n\'existe pas.', $name)
            );
        }

        return $this->commands[$name];
    }

    /**
     * Vérifie si une commande existe dans la collection.
     *
     * Détermine si une commande avec le nom spécifié est présente dans la collection.
     *
     * @param string $name Le nom de la commande à vérifier
     *
     * @return bool True si la commande existe, false sinon
     */
    public function has(string $name): bool
    {
        return isset($this->commands[$name]);
    }

    /**
     * Retourne toutes les commandes de la collection.
     *
     * Récupère l'ensemble des commandes enregistrées sous forme de tableau associatif
     * où les clés sont les noms des commandes et les valeurs sont les instances.
     *
     * @return array<string, AbstractCommand> Tableau de toutes les commandes
     */
    public function all(): array
    {
        return $this->commands;
    }

    /**
     * Retourne les noms de toutes les commandes.
     *
     * Récupère uniquement les noms des commandes enregistrées, sans les instances.
     * Utile pour afficher la liste des commandes disponibles.
     *
     * @return array<int, string> Tableau des noms de commandes
     */
    public function getNames(): array
    {
        return array_keys($this->commands);
    }

    /**
     * Compte le nombre de commandes dans la collection.
     *
     * Retourne le nombre total de commandes enregistrées dans la collection.
     *
     * @return int Le nombre de commandes
     */
    public function count(): int
    {
        return count($this->commands);
    }

    /**
     * Supprime une commande de la collection.
     *
     * Retire une commande de la collection en utilisant son nom.
     * Si la commande n'existe pas, aucune action n'est effectuée.
     *
     * @param string $name Le nom de la commande à supprimer
     *
     * @return void
     */
    public function remove(string $name): void
    {
        unset($this->commands[$name]);
    }

    /**
     * Vide complètement la collection.
     *
     * Supprime toutes les commandes de la collection, la réinitialisant
     * à un état vide.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->commands = [];
    }

    /**
     * Fusionne une autre collection dans celle-ci.
     *
     * Ajoute toutes les commandes d'une autre CommandCollection à cette collection.
     * Les commandes avec des noms identiques seront écrasées par celles de la
     * collection source.
     *
     * @param CommandCollection $collection La collection à fusionner
     *
     * @return void
     */
    public function merge(CommandCollection $collection): void
    {
        foreach ($collection->all() as $command) {
            $this->add($command);
        }
    }

    /**
     * Filtre les commandes par un critère personnalisé.
     *
     * Retourne une nouvelle collection contenant uniquement les commandes
     * qui satisfont le callback fourni.
     *
     * @param callable $callback Fonction de filtrage (AbstractCommand $command): bool
     *
     * @return CommandCollection Nouvelle collection avec les commandes filtrées
     */
    public function filter(callable $callback): CommandCollection
    {
        $filtered = new self();

        foreach ($this->commands as $command) {
            if ($callback($command)) {
                $filtered->add($command);
            }
        }

        return $filtered;
    }
}