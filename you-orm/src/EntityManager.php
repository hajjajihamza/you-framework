<?php

namespace YouOrm;

use ReflectionClass;
use YouOrm\Connection\DBConnection;
use YouOrm\Hydrator\ObjectHydrator;
use YouOrm\Query\QueryBuilder;
use YouOrm\Schema\Attribute\Table;

class EntityManager
{
    private ObjectHydrator $hydrator;
    private array $persistedEntities = [];

    public function __construct(private readonly DBConnection $connection)
    {
        $this->hydrator = new ObjectHydrator();
    }

    public function getQueryBuilder(): QueryBuilder
    {
       return new QueryBuilder($this->connection->getConnection());
    }

    public function find(string $class, $id): ?object
    {
        $tableName = $this->getTableName($class);
        $primaryKey = $this->getPrimaryKeyName($class);

        $qb = $this->getQueryBuilder()
            ->select('*')
            ->from($tableName)
            ->where($primaryKey.'=:id')
            ->setParameter('id', $id)
            ->limit(1)
        ;

        $result = $qb->getSingleResult();
        if (!$result) {
            return null;
        }

        $entity = new $class();
        $this->hydrator->hydrate($entity, $result);

        return $entity;
    }

    public function persist(object $entity): void
    {
        if (!in_array($entity, $this->persistedEntities, true)) {
            $this->persistedEntities[] = $entity;
        }
    }

    public function flush(): void
    {
        foreach ($this->persistedEntities as $entity) {
            $this->save($entity);
        }
        $this->persistedEntities = [];
    }

    private function save(object $entity): void
    {
        $tableName = $this->getTableName(get_class($entity));
        $primaryKeyValue = $this->getPrimaryKeyValue($entity);
        $primaryKeyName = $this->hydrator->getPrimaryKeyName(get_class($entity));
        $data = $this->hydrator->extract($entity);

        if ($primaryKeyName && $primaryKeyValue !== null) {
            $this->update($tableName, $data, $primaryKeyName);
        } else {
            $this->insert($tableName, $data, $primaryKeyName, $entity);
        }
    }

    private function insert(string $table, array $data, string $primaryKeyName, object $entity): void
    {
        //: TODO: Améliorer la logic si le primary key ne pas être auto-increment
        // Remove primary key
        unset($data[$primaryKeyName]);
        $columns = array_keys($data);

        $placeholders = array_map(static fn($col) => ":$col", $columns);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->connection->getConnection()->prepare($sql);
        $stmt->execute($data);

        // load inserted ID
        $id = $this->connection->getConnection()->lastInsertId();
        $property = new \ReflectionProperty($entity, $primaryKeyName);

        // Cast ID to correct type
        $type = $property->getType();
        if ($type && $type->getName() === 'int') {
            $id = (int) $id;
        }

        $property->setValue($entity, $id);
    }

    private function update(string $table, array $data, string $primaryKeyName): void
    {
        $id = $data[$primaryKeyName];
        unset($data[$primaryKeyName]);

        $sets = array_map(static fn($col) => "$col = :$col", array_keys($data));

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = :_primary_id",
            $table,
            implode(', ', $sets),
            $primaryKeyName
        );

        $data['_primary_id'] = $id;

        $stmt = $this->connection->getConnection()->prepare($sql);
        $stmt->execute($data);
    }

    public function remove(object $entity): void
    {
        $class = get_class($entity);
        $table = $this->getTableName($class);
        $primaryKeyName = $this->getPrimaryKeyName($class);
        $id = $this->getPrimaryKeyValue($entity);

        if ($id === null) {
            return;
        }

        $sql = sprintf("DELETE FROM %s WHERE %s = :id", $table, $primaryKeyName);
        $stmt = $this->connection->getConnection()->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    public function getTableName(string $class): string
    {
        $reflection = new ReflectionClass($class);
        $attributes = $reflection->getAttributes(Table::class);

        if (empty($attributes)) {
            // Fallback to class name lowercase or error?
            // Assuming attribute is mandatory as per requirements
            throw new \RuntimeException("Class $class is missing #[Table] attribute.");
        }

        return $attributes[0]->newInstance()->name;
    }

    public function getPrimaryKeyName(string $class): string
    {
        // Simple helper, could be optimized
        $pk = $this->hydrator->getPrimaryKeyName($class);
        if (!$pk) {
            throw new \RuntimeException("Class $class has no primary key defined.");
        }
        return $pk;
    }

    public function getPrimaryKeyValue(object $entity): ?int
    {
        return $this->hydrator->getPrimaryKeyValue($entity);
    }


    public function getConnection(): DBConnection
    {
        return $this->connection;
    }

    public function getHydrator(): ObjectHydrator
    {
        return $this->hydrator;
    }
}
