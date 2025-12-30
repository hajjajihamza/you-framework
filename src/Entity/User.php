<?php

namespace App\Entity;

use YouOrm\Attribute\Table;
use YouOrm\Attribute\Column;
use YouOrm\Attribute\PrimaryKey;
use YouOrm\Type\ColumnType;

#[Table(name: 'users')]
class User
{
    #[PrimaryKey(autoIncrement: true)]
    #[Column(name: 'id', type: ColumnType::INTEGER)]
    private int $id;

    #[Column(name: 'username', type: ColumnType::STRING, length: 150, unique: true)]
    private string $username;

    #[Column(name: 'email', type: ColumnType::STRING, length: 255, unique: true)]
    private string $email;

    #[Column(name: 'active', type: ColumnType::BOOLEAN, default: true)]
    private bool $active;
}
