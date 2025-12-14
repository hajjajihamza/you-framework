<?php

declare(strict_types=1);

namespace YouConsole\Helper;

use YouConsole\Input\Input;
use YouConsole\Output\Output;
use YouConsole\Command\AbstractCommand;

/**
 * Commande intégrée pour lister toutes les commandes disponibles.
 *
 * Cette commande affiche un tableau formaté de toutes les commandes
 * enregistrées dans l'application avec leur description.
 *
 * @package YouConsole\Helper
 */
class ListCommand extends AbstractCommand
{
    /** @var array<AbstractCommand> Liste des commandes disponibles */
    private array $commands = [];

    /**
     * Configure la commande list.
     */
    protected function configure(): void
    {
        $this->setName('list')
            ->setDescription('Liste toutes les commandes disponibles');
    }

    /**
     * Définit les commandes à afficher.
     *
     * @param array<AbstractCommand> $commands Liste des commandes
     */
    public function setCommands(array $commands): void
    {
        $this->commands = $commands;
    }

    /**
     * Exécute la commande list.
     *
     * @param Input $input Entrées de la commande
     * @param Output $output Sortie pour afficher des messages
     * @return int Code de retour (0 = succès)
     */
    protected function execute(Input $input, Output $output): int
    {
        $output->writeln();
        $output->writeln('<info>Commandes disponibles</info>');
        $output->writeln('<comment>=====================</comment>');
        $output->writeln();

        // Trier les commandes par nom
        $sortedCommands = $this->commands;
        usort($sortedCommands, static fn(AbstractCommand $a, AbstractCommand $b) => strcmp($a->getName(), $b->getName()));

        // Calculer la largeur maximale pour l'alignement
        $maxLength = 0;
        foreach ($sortedCommands as $command) {
            $length = strlen($command->getName());
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }

        // Afficher chaque commande
        foreach ($sortedCommands as $command) {
            $name = str_pad($command->getName(), $maxLength + 2);
            $description = $command->getDescription();

            $output->writeln(sprintf(
                '  <success>%s</success> %s',
                $name,
                $description
            ));
        }

        $output->writeln();
        $output->writeln('<comment>Usage :</comment>');
        $output->writeln('  you <commande> [arguments] [options]');
        $output->writeln();

        return 0;
    }
}
