<?php

namespace Test\Entity;

use YouOrm\Attribute\Column;
use YouOrm\Attribute\Table;
use YouOrm\Type\ColumnType;

#[Table(name: 'users')]
class User
{
    #[Column(type: ColumnType::INTEGER, autoIncrement: true, primaryKey: true)]
    private int $id;

    #[Column(type: ColumnType::STRING, length: 150)]
    private string $username;

    #[Column(type: ColumnType::STRING, unique: true)]
    private string $email;

    #[Column(type: ColumnType::DATETIME)]
    private string $createdAt;
}
