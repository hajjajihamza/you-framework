<?php

require_once __DIR__ . '/vendor/autoload.php';

use YouOrm\Grammar\DDL\MySqlGrammarDDL;
use YouOrm\Grammar\DDL\PostgreSqlGrammarDDL;
use YouOrm\Grammar\DDL\SqlServerGrammarDDL;
use YouOrm\Grammar\DDL\SqliteGrammarDDL;
use YouOrm\Type\ColumnType;

$table = 'users';
$columns = [
    [
        'name' => 'id',
        'type' => ColumnType::BIGINT,
        'primary_key' => true,
        'auto_increment' => true,
        'nullable' => false,
    ],
    [
        'name' => 'username',
        'type' => ColumnType::STRING,
        'length' => 100,
        'nullable' => false,
        'unique' => true,
    ],
    [
        'name' => 'email',
        'type' => ColumnType::STRING,
        'length' => 255,
        'nullable' => true,
    ],
    [
        'name' => 'is_active',
        'type' => ColumnType::BOOLEAN,
        'default' => true,
    ],
    [
        'name' => 'created_at',
        'type' => ColumnType::DATETIME,
        'nullable' => false,
    ],
];

$grammars = [
    'MySQL' => new MySqlGrammarDDL(),
    'PostgreSQL' => new PostgreSqlGrammarDDL(),
    'SQL Server' => new SqlServerGrammarDDL(),
    'SQLite' => new SqliteGrammarDDL(),
];

foreach ($grammars as $name => $grammar) {
    echo "--- $name ---\n";
    echo $grammar->compileCreateTable($table, $columns) . ";\n\n";
}
