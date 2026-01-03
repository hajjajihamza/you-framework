<?php

namespace YouOrm\Migration;

use YouOrm\Connection\DBConnection;

abstract class AbstractMigration
{
    protected DBConnection $connection;

    public function __construct(DBConnection $connection)
    {
        $this->connection = $connection;
    }

    abstract public function up(): void;

    abstract public function down(): void;

    protected function execute(string $sql): void
    {
        $this->connection->getConnection()->exec($sql);
    }
}
