<?php

declare(strict_types=1);

namespace YouConsole\Helper;

use YouConfig\Config;
use YouConsole\Command\AbstractCommand;
use YouConsole\Input\Input;
use YouConsole\Output\Output;
use YouKernel\Component\Container\Container;

/**
 * Commande pour démarrer le serveur de développement PHP.
 *
 * Cette commande démarre un serveur de développement PHP intégré
 * en utilisant la configuration app.url ou des options personnalisées.
 *
 * @package YouConsole\Helper
 */
class ServeCommand extends AbstractCommand
{
    /**
     * Constructeur de la commande.
     *
     * @param Container $container Container pour accéder à la configuration
     */
    public function __construct(private Container $container)
    {
        parent::__construct();
    }

    /**
     * Configure la commande serve.
     */
    protected function configure(): void
    {
        $this->setName('serve')
            ->setDescription('Démarre le serveur de développement PHP')
            ->addOption('host', null, false, 'L\'hôte du serveur (par défaut: extrait de app.url)')
            ->addOption('port', 'p', false, 'Le port du serveur (par défaut: extrait de app.url)')
            ->addOption('docroot', 't', false, 'Le répertoire racine du serveur (par défaut: public)');
    }

    /**
     * Exécute la commande serve.
     *
     * @param Input $input Entrées de la commande
     * @param Output $output Sortie pour afficher des messages
     * @return int Code de retour (0 = succès)
     */
    protected function execute(Input $input, Output $output): int
    {
        // Récupérer la configuration
        /** @var Config $config */
        $config = $this->container->get(Config::class);

        // Récupérer l'URL depuis la configuration
        $appUrl = $config->get('app.url', 'http://127.0.0.1:8000');

        // Parser l'URL pour extraire host et port
        $urlParts = parse_url($appUrl);
        $defaultHost = $urlParts['host'] ?? 'localhost';
        $defaultPort = $urlParts['port'] ?? 8000;

        // Permettre de surcharger avec les options
        $host = $input->getOption('host') ?? $defaultHost;
        $port = $input->getOption('port') ?? $defaultPort;
        $docroot = $input->getOption('docroot') ?? 'public';

        // Construire l'URL complète
        $serverUrl = "http://{$host}:{$port}";

        // Afficher les informations de démarrage
        $output->writeln();
        $output->writeln('<info>Serveur de développement You Framework</info>');
        $output->writeln('<comment>=====================================</comment>');
        $output->writeln();
        $output->writeln("  <success>URL:</success>      {$serverUrl}");
        $output->writeln("  <success>Docroot:</success>  {$docroot}");
        $output->writeln();
        $output->writeln('<comment>Appuyez sur Ctrl+C pour arrêter le serveur</comment>');
        $output->writeln();

        // Vérifier que le répertoire docroot existe
        if (!is_dir($docroot)) {
            $output->error("Le répertoire '{$docroot}' n'existe pas.");
            return self::STATUS_ERROR;
        }

        // Construire et exécuter la commande
        $command = sprintf(
            'php -S %s:%s -t %s',
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($docroot)
        );

        // Exécuter la commande (passthru permet de voir la sortie en temps réel)
        passthru($command, $exitCode);

        return $exitCode === 0 ? self::STATUS_SUCCESS : self::STATUS_ERROR;
    }
}
