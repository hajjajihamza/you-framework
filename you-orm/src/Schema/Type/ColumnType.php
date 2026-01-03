<?php

namespace YouOrm\Schema\Type;

/**
 * Interface définissant les types de colonnes disponibles pour l'ORM.
 *
 * Cette interface regroupe les constantes représentant les différents types SQL
 * supportés par le mappage objet-relationnel.
 */
interface ColumnType
{
    // Types Numériques
    public const string SMALLINT = 'smallint';
    public const string INTEGER = 'integer';
    public const string BIGINT = 'bigint';
    public const string DECIMAL = 'decimal';
    public const string SMALL_FLOAT = 'small_float';
    public const string FLOAT = 'float';

    // Types de chaînes de caractères
    public const string STRING = 'string';
    public const string TEXT = 'text';
    public const string UUID = 'uuid';

    // Types Binaires
    public const string BINARY = 'binary';
    public const string BLOB = 'blob';

    // Types Booléens
    public const string BOOLEAN = 'boolean';

    // Types Temporels
    public const string DATE = 'date';
    public const string DATETIME = 'datetime';
    public const string TIME = 'time';
    public const string DATETIME_TZ = 'datetime_tz';

    // Types lités
    public const string JSON = 'json';
    public const string ARRAY = 'array';
}