<?php

namespace YouOrm\Schema\Introspector;

use PDO;
use YouOrm\Connection\DBConnection;
use YouOrm\Schema\Attribute\Column;
use YouOrm\Schema\Attribute\Table;
use YouOrm\Schema\Schema;
use YouOrm\Schema\Type\ColumnType;

/**
 * Class MySqlIntrospector
 * MySQL implementation for database schema introspection.
 */
class MySqlIntrospector implements DatabaseSchemaIntrospectorInterface
{
    public function __construct(
        private DBConnection $connection
    ) {
    }

    public function introspect(): Schema
    {
        $pdo = $this->connection->getConnection();
        $stmt = $pdo->query("SHOW TABLES");
        $tables = [];

        while ($tableName = $stmt->fetchColumn()) {
            $table = $this->introspectTable($tableName);
            if ($table) {
                $tables[] = $table;
            }
        }

        return new Schema($tables);
    }

    public function introspectTable(string $tableName): ?Table
    {
        $pdo = $this->connection->getConnection();

        // Fetch columns
        $stmt = $pdo->query("SHOW FULL COLUMNS FROM `$tableName`");
        $rawColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rawColumns)) {
            return null;
        }

        $columns = [];
        foreach ($rawColumns as $rawColumn) {
            $columns[] = $this->mapColumn($rawColumn);
        }

        return new Table($tableName)->setColumns($columns);
    }

    private function mapColumn(array $rawColumn): Column
    {
        $typeInfo = $this->parseType($rawColumn['Type']);

        return new Column(
            name: $rawColumn['Field'],
            type: $typeInfo['type'],
            length: $typeInfo['length'],
            nullable: $rawColumn['Null'] === 'YES',
            default: $rawColumn['Default'],
            unique: $rawColumn['Key'] === 'UNI',
            primaryKey: $rawColumn['Key'] === 'PRI',
            autoIncrement: str_contains($rawColumn['Extra'], 'auto_increment'),
            precision: $typeInfo['precision'],
            scale: $typeInfo['scale']
        );
    }

    private function parseType(string $mysqlType): array
    {
        $type = strtoupper($mysqlType);
        $length = null;
        $precision = null;
        $scale = null;

        $typeBase = $type;
        if (preg_match('/(\w+)\((\d+)(?:,(\d+))?\)/', $type, $matches)) {
            $typeBase = $matches[1];
            if (isset($matches[3])) {
                $precision = (int) $matches[2];
                $scale = (int) $matches[3];
            } else if($typeBase === 'VARCHAR' || $typeBase === 'VARBINARY') {
                $length = (int) $matches[2];
            }
        }

        $mappedType = match ($typeBase) {
            'SMALLINT' => ColumnType::SMALLINT,
            'INT' => ColumnType::INTEGER,
            'BIGINT' => ColumnType::BIGINT,
            'DECIMAL' => ColumnType::DECIMAL,
            'FLOAT' => ColumnType::SMALL_FLOAT,
            'DOUBLE' => ColumnType::FLOAT,
            'TEXT' => ColumnType::TEXT,
            'CHAR' => ColumnType::UUID,
            'VARBINARY' => ColumnType::BINARY,
            'BLOB' => ColumnType::BLOB,
            'TINYINT' => ColumnType::BOOLEAN,
            'DATE' => ColumnType::DATE,
            'DATETIME' => ColumnType::DATETIME,
            'TIMESTAMP' => ColumnType::DATETIME_TZ,
            'TIME' => ColumnType::TIME,
            'LONGTEXT' => ColumnType::ARRAY,
            'JSON' => ColumnType::JSON,
            default => ColumnType::STRING,
        };

        return [
            'type' => $mappedType,
            'length' => $length,
            'precision' => $precision,
            'scale' => $scale
        ];
    }
}
