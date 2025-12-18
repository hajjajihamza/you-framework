<?php

namespace YouOrm\Type;

/**
 * Interface définissant les types de colonnes disponibles pour l'ORM.
 *
 * Cette interface regroupe les constantes représentant les différents types SQL
 * supportés par le mappage objet-relationnel.
 */
interface ColumnType
{
    public const string STRING = 'string';
    public const string INTEGER = 'integer';
    public const string FLOAT = 'float';
    public const string TEXT = 'text';
    public const string BOOLEAN = 'boolean';
    public const string DATE = 'date';
    public const string DATETIME = 'datetime';
    public const string TIME = 'time';
    public const string JSON = 'json';
    public const string BLOB = 'blob';
    public const string DECIMAL = 'decimal';
}
