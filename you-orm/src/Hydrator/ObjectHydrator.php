<?php

namespace YouOrm\Hydrator;

use DateTimeInterface;
use ReflectionClass;
use ReflectionProperty;
use YouOrm\Schema\Attribute\Column;

class ObjectHydrator
{
    public function hydrate(object $entity, array $data): void
    {
        $reflection = new ReflectionClass($entity);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $columnAttribute = $property->getAttributes(Column::class);
            if (empty($columnAttribute)) {
                continue;
            }

            /** @var Column $column */
            $column = $columnAttribute[0]->newInstance();
            $columnName = $column->getName();

            if (array_key_exists($columnName, $data)) {
                $value = $this->castValue($property, $data[$columnName]);
                $property->setValue($entity, $value);
            }
        }
    }

    private function castValue(ReflectionProperty $property, $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $type = $property->getType();
        if (!$type) {
            return $value;
        }

        $typeName = $type->getName();

        // TODO: Améliorer la gestion des types
        return match ($typeName) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => (bool) $value,
            'DateTime' => new \DateTime($value),
            'DateTimeImmutable' => new \DateTimeImmutable($value),
            default => $value,
        };
    }

    public function extract(object $entity): array
    {
        $reflection = new ReflectionClass($entity);
        $properties = $reflection->getProperties();
        $data = [];

        foreach ($properties as $property) {
            $columnAttribute = $property->getAttributes(Column::class);
            if (empty($columnAttribute)) {
                continue;
            }

            /** @var Column $column */
            $column = $columnAttribute[0]->newInstance();
            $value = $property->getValue($entity);

            // TODO: Amplier la gestion des types
            if ($value instanceof DateTimeInterface ) {
                $value = $value->format('Y-m-d H:i:s');
            }

            $data[$column->getName()] = $value;
        }

        return $data;
    }

    public function getPrimaryKeyName(string $class): ?string
    {
        $reflection = new ReflectionClass($class);
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(Column::class);
            if (!empty($attributes)) {
                /** @var Column $attribute */
                $attribute = $attributes[0]->newInstance();
                if ($attribute->isPrimaryKey()) {
                    return $property->getName();
                }
            }
        }
        return null;
    }

    public function getPrimaryKeyValue(object $entity): ?int
    {
       $reflection = new ReflectionClass($entity);
       $property = $reflection->getProperty($this->getPrimaryKeyName(get_class($entity)));
       return $property->getValue($entity);
    }
}
