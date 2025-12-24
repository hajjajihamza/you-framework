<?php

namespace YouKernel\Bootstrap;

use YouConfig\Config;
use YouKernel\Component\Container\Container;
use YouOrm\Connection\ConnectionConfig;
use YouOrm\Connection\ConnectionFactory;
use YouOrm\Connection\DBConnection;
use YouOrm\EntityManager;

/**
 * Classe OrmBootstrapper
 *
 * Gère l'initialisation du composant ORM (YouOrm).
 * Elle configure la connexion à la base de données et l'EntityManager.
 *
 * @package YouKernel\Bootstrap
 * @author  Hamza Hajjaji <https://github.com/hajjvero>
 */
final class OrmBootstrapper
{
    /**
     * Initialise et enregistre l'EntityManager dans le conteneur.
     *
     * @param Container $container Conteneur de dépendances
     *
     * @return EntityManager Instance de l'EntityManager configuré
     */
    public function boot(Container $container): EntityManager
    {
        /** @var Config $config */
        $config = $container->get(Config::class);

        // Récupération de la configuration de la base de données
        $dbConfigData = $config->get('database');

        if (!$dbConfigData) {
            throw new \RuntimeException("Database configuration is missing in config/database.php");
        }

        // Initialisation de la connexion via la factory
        $connection = ConnectionFactory::createFromConfig($dbConfigData);

        // Initialisation de l'EntityManager
        $entityManager = new EntityManager($connection);

        // Enregistrement dans le conteneur
        $container->set(DBConnection::class, $connection);
        $container->set(EntityManager::class, $entityManager);

        return $entityManager;
    }
}
