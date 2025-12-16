<?php

namespace YouConsole;

use YouConsole\Command\AbstractCommand;
use YouConsole\Command\CommandCollection;
use YouConsole\Command\CommandDiscovery;
use YouConsole\Helper\ListCommand;
use YouConsole\Input\Input;
use YouConsole\Output\Output;
use YouKernel\Component\Container\Container;

class YouConsoleKernel
{
    private CommandCollection $commandCollection;

    /** @var Output Gestionnaire de sortie */
    private Output $output;

    /** @var ?string Répertoire des commandes pour l'auto-discovery */
    private ?string $commandsDirectory = null;

    public function __construct(private Container $container)
    {
        $this->output = new Output();
        $this->commandCollection = new CommandCollection();
        $this->commandsDirectory ??= $container->get('project_dir') . '/src/Controller';
    }

    /**
     * Point d'entrée de la console
     *
     * @param array<string>|null $argv Arguments de la ligne de commande (null = utiliser $_SERVER['argv'])
     * @return int Code de retour (0 = succès, autre = erreur)
     */
    public function run(?array $argv = null): int {

        try {
            // Utiliser les arguments globaux si non fournis
            if ($argv === null) {
                $argv = $_SERVER['argv'] ?? [];
            }

            // Auto-découverte des commandes
            $this->autoDiscoverCommands();

            // Parser les arguments pour obtenir le nom de la commande
            $commandName = $this->extractCommandName($argv);

            // Si aucune commande n'est spécifiée, afficher la liste
            if (empty($commandName)) {
                $commandName = 'list';
            }

            // Trouver et exécuter la commande
            $command = $this->findCommand($commandName);

            // Créer l'objet Input avec les définitions de la commande
            $input = new Input($argv, $command->getArguments(), $command->getOptions());

            // Passer la liste des commandes à ListCommand si c'est celle-ci
            if ($command instanceof ListCommand) {
                $command->setCommands($this->commandCollection->all());
            }

            // Exécuter la commande
            return $command->run($input, $this->output);
        } catch (\Exception $e) {
            $this->output->error($e->getMessage());
            return AbstractCommand::STATUS_ERROR;
        }
    }

    /**
     * Enregistre manuellement une commande.
     *
     * @param AbstractCommand ...$commands Commandes à enregistrer
     * @return YouConsoleKernel
     */
    public function registerCommand(AbstractCommand ...$commands): static
    {
        array_map(static fn(AbstractCommand $command) => $this->commandCollection->add($command), $commands);

        return $this;
    }

    /**
     * Découvre automatiquement les commandes dans le répertoire configuré.
     */
    private function autoDiscoverCommands(): void
    {
        $discovery = new CommandDiscovery($this->container);
        $discoveredCommands = $discovery->discover($this->commandsDirectory);

        $this->registerCommand(...$discoveredCommands);
    }

    /**
     * Trouve une commande par son nom.
     *
     * @param string $name Nom de la commande
     * @return AbstractCommand La commande trouvée ou null
     */
    public function findCommand(string $name): AbstractCommand
    {
        return $this->commandCollection->get($name);
    }

    /**
     * Extrait le nom de la commande des arguments.
     *
     * @param array<string> $argv Arguments de la ligne de commande
     * @return string Nom de la commande ou chaîne vide
     */
    private function extractCommandName(array $argv): string
    {
        // Le premier argument est le nom du script, le second est la commande
        return $argv[1] ?? '';
    }

    /**
     * @return string
     */
    public function getCommandsDirectory(): string
    {
        return $this->commandsDirectory;
    }

    /**
     * @param string $commandsDirectory
     * @return YouConsoleKernel
     */
    public function setCommandsDirectory(string $commandsDirectory): self
    {
        $this->commandsDirectory = $commandsDirectory;

        return $this;
    }


}