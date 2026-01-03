<?php

namespace YouOrm\Schema\Introspector;

use YouOrm\Schema\Attribute\Table;
use YouOrm\Schema\Schema;

/**
 * Interface DatabaseSchemaIntrospectorInterface
 * Defines the contract for reading the schema from a live database.
 */
interface DatabaseSchemaIntrospectorInterface
{
    /**
     * Read the entire schema from the database.
     *
     * @return Schema
     */
    public function introspect(): Schema;

    /**
     * Read a specific table schema from the database.
     *
     * @param string $tableName
     * @return Table|null
     */
    public function introspectTable(string $tableName): ?Table;
}
