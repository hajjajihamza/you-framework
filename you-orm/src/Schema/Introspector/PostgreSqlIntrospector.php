<?php

namespace YouOrm\Schema\Introspector;

use PDO;
use YouOrm\Connection\DBConnection;
use YouOrm\Schema\Attribute\Column;
use YouOrm\Schema\Attribute\Table;
use YouOrm\Schema\Schema;
use YouOrm\Schema\Type\ColumnType;

/**
 * Class PostgreSqlIntrospector
 * PostgreSQL implementation for database schema introspection.
 */
class PostgreSqlIntrospector implements DatabaseSchemaIntrospectorInterface
{
    public function __construct(
        private DBConnection $connection,
        private readonly string $schema = 'public'
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function introspect(): Schema
    {
        $pdo = $this->connection->getConnection();

        // List tables in the specified schema
        $stmt = $pdo->prepare("
            SELECT tablename 
            FROM pg_catalog.pg_tables 
            WHERE schemaname = :schema
            ORDER BY tablename
        ");
        $stmt->execute(['schema' => $this->schema]);

        $tables = [];
        while ($tableName = $stmt->fetchColumn()) {
            $table = $this->introspectTable($tableName);
            if ($table) {
                $tables[] = $table;
            }
        }

        return new Schema($tables);
    }

    /**
     * {@inheritDoc}
     */
    public function introspectTable(string $tableName): ?Table
    {
        $pdo = $this->connection->getConnection();

        // 1. Fetch column metadata from information_schema
        $stmt = $pdo->prepare("
            SELECT 
                column_name, 
                data_type, 
                is_nullable, 
                column_default, 
                character_maximum_length, 
                numeric_precision, 
                numeric_scale,
                udt_name
            FROM information_schema.columns 
            WHERE table_schema = :schema 
              AND table_name = :table
            ORDER BY ordinal_position
        ");
        $stmt->execute(['schema' => $this->schema, 'table' => $tableName]);
        $rawColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rawColumns)) {
            return null;
        }

        // 2. Fetch constraints (Primary Key, Unique)
        $constraintsStmt = $pdo->prepare("
            SELECT kcu.column_name, tc.constraint_type 
            FROM information_schema.table_constraints AS tc 
            JOIN information_schema.key_column_usage AS kcu 
              ON tc.constraint_name = kcu.constraint_name 
             AND tc.table_schema = kcu.table_schema 
            WHERE tc.table_name = :table 
              AND tc.table_schema = :schema
        ");
        $constraintsStmt->execute(['schema' => $this->schema, 'table' => $tableName]);
        $rawConstraints = $constraintsStmt->fetchAll(PDO::FETCH_ASSOC);

        $primaryKeys = [];
        $uniqueKeys = [];
        foreach ($rawConstraints as $constraint) {
            if ($constraint['constraint_type'] === 'PRIMARY KEY') {
                $primaryKeys[] = $constraint['column_name'];
            } elseif ($constraint['constraint_type'] === 'UNIQUE') {
                $uniqueKeys[] = $constraint['column_name'];
            }
        }

        // 3. Map columns
        $columns = [];
        foreach ($rawColumns as $rawColumn) {
            $columns[] = $this->mapColumn($rawColumn, $primaryKeys, $uniqueKeys);
        }

        return new Table($tableName)->setColumns($columns);
    }

    private function mapColumn(array $raw, array $primaryKeys, array $uniqueKeys): Column
    {
        $name = $raw['column_name'];
        $rawType = $raw['data_type'];
        $udtName = $raw['udt_name']; // Useful for some specific types like uuid

        $length = $raw['character_maximum_length'] ? (int) $raw['character_maximum_length'] : null;
        $precision = $raw['numeric_precision'] ? (int) $raw['numeric_precision'] : null;
        $scale = $raw['numeric_scale'] ? (int) $raw['numeric_scale'] : null;

        // Common PostgreSQL types to ColumnType mapping
        // Postgres data_type often returns "character varying", "integer", etc.
        $type = match (strtolower($rawType)) {
            'smallint' => ColumnType::SMALLINT,
            'integer' => ColumnType::INTEGER,
            'bigint' => ColumnType::BIGINT,
            'numeric', 'decimal' => ColumnType::DECIMAL,
            'real' => ColumnType::SMALL_FLOAT,
            'double precision' => ColumnType::FLOAT,
            'text' => ColumnType::TEXT,
            'character varying', 'varchar' => ColumnType::STRING,
            'character', 'char' => ColumnType::STRING,
            'bytea' => ColumnType::BINARY,
            'boolean' => ColumnType::BOOLEAN,
            'date' => ColumnType::DATE,
            'timestamp without time zone', 'timestamp' => ColumnType::DATETIME,
            'timestamp with time zone' => ColumnType::DATETIME_TZ,
            'time without time zone', 'time' => ColumnType::TIME,
            'json', 'jsonb' => ColumnType::JSON,
            'uuid' => ColumnType::UUID,
            'user-defined' => ($udtName === 'uuid' ? ColumnType::UUID : ColumnType::STRING),
            default => ColumnType::STRING,
        };

        // Detect auto-increment (SERIAL types use nextval() as default)
        $isAutoIncrement = false;
        if (
            $raw['column_default'] && (
                str_contains($raw['column_default'], 'nextval') ||
                str_contains($raw['column_default'], 'identity')
            )
        ) {
            $isAutoIncrement = true;
        }

        // Clean up default value string (e.g., "'default_val'::character varying" -> "default_val")
        $default = $raw['column_default'];
        if ($default && str_starts_with($default, "'") && str_contains($default, "'::")) {
            $default = substr($default, 1, strpos($default, "'::") - 1);
        } elseif ($isAutoIncrement) {
            // If auto-increment, we usually don't want the nextval() as the "default value" in our Column attribute
            $default = null;
        }

        return new Column(
            name: $name,
            type: $type,
            length: $length,
            nullable: $raw['is_nullable'] === 'YES',
            default: $default,
            unique: in_array($name, $uniqueKeys),
            primaryKey: in_array($name, $primaryKeys),
            autoIncrement: $isAutoIncrement,
            precision: $precision,
            scale: $scale
        );
    }
}
