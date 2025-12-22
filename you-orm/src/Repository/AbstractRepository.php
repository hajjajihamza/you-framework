<?php

namespace YouOrm\Repository;

use ReflectionClass;
use YouOrm\Attribute\Table;
use YouOrm\EntityManager;
use YouOrm\Query\QueryBuilder;

/**
 * Classe abstraite représentant un repository pour gérer les entités.
 * 
 * Cette classe fournit des méthodes de base pour interagir avec la base de données
 * en utilisant l'EntityManager et le QueryBuilder.
 *
 * @package YouOrm
 * @abstract
 * @author Hamza Hajjaji <hajjvero@gmail.com>
 */
abstract class AbstractRepository
{
    /**
     * Constructeur du repository.
     *
     * @param EntityManager $em L'instance de l'EntityManager pour interagir avec la base de données
     * @param string $entityClass Le nom complet de la classe de l'entité gérée par ce repository
     */
    public function __construct(
        protected EntityManager $em,
        protected string $entityClass
    ) {
    }

    /**
     * Récupère le nom de la table associée à l'entité.
     *
     * Utilise la reflection pour obtenir les attributs de la classe d'entité
     * et extrait le nom de la table à partir de l'attribut #[Table].
     *
     * @return string Le nom de la table associée à l'entité
     */
    protected function getTableName(): string
    {
        return $this->em->getTableName($this->entityClass);
    }

    /**
     * Crée une instance de QueryBuilder préconfigurée pour sélectionner toutes les colonnes
     * depuis la table associée à l'entité.
     *
     * @return QueryBuilder Une instance de QueryBuilder configurée pour la table de l'entité
     */
    protected function createQueryBuilder(): QueryBuilder
    {
        return $this->em->getQueryBuilder()
            ->select('*')
            ->from($this->getTableName())
            ;
    }

    /**
     * Trouve une entité par son identifiant.
     *
     * @param mixed $id L'identifiant de l'entité
     * @return object|null L'entité correspondante ou null si elle n'existe pas
     */
    public function find(mixed $id): ?object
    {
        $results = $this->findBy([$this->em->getPrimaryKeyName($this->entityClass) => $id], null, 1);

        return $results[0] ?? null;
    }

    /**
     * Récupère toutes les entités de la table associée.
     *
     * Exécute une requête SELECT * sur la table et hydrate les résultats
     * dans des instances de la classe d'entité.
     *
     * @return array Un tableau d'instances d'entités
     */
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder();

        $results = $qb->getResult();

        $entities = [];
        $hydrator = $this->em->getHydrator();
        foreach ($results as $row) {
            $entity = new $this->entityClass();
            $hydrator->hydrate($entity, $row);
            $entities[] = $entity;
        }

        return $entities;
    }

    /**
     * Recherche des entités selon des critères spécifiés.
     *
     * @param array $criteria Un tableau associatif de critères de recherche (colonne => valeur)
     * @param array|null $orderBy Un tableau associatif pour trier les résultats (colonne => ordre)
     * @param int|null $limit Le nombre maximal d'entités à retourner
     * @param int|null $offset Le décalage pour la pagination
     * @return array Un tableau d'instances d'entités correspondant aux critères
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->createQueryBuilder();

        foreach ($criteria as $column => $value) {
            $qb->andWhere("$column = :$column");
            $qb->setParameter($column, $value);
        }

        if ($orderBy) {
            foreach ($orderBy as $sort => $order) {
                $qb->orderBy($sort, $order);
            }
        }

        if ($limit) {
            $qb->limit($limit);
        }

        $results = $qb->getResult();

        $entities = [];
        $hydrator = $this->em->getHydrator();
        foreach ($results as $row) {
            $entity = new $this->entityClass();
            $hydrator->hydrate($entity, $row);
            $entities[] = $entity;
        }

        return $entities;
    }

    /**
     * Recherche une seule entité selon des critères spécifiés.
     *
     * @param array $criteria Un tableau associatif de critères de recherche (colonne => valeur)
     * @return object|null L'instance de l'entité trouvée ou null si aucune entité ne correspond
     */
    public function findOneBy(array $criteria): ?object
    {
        $results = $this->findBy($criteria, null, 1);
        return $results[0] ?? null;
    }
}