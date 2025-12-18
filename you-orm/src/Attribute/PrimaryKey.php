<?php

namespace YouOrm\Attribute;

use Attribute;

/**
 * Attribut permettant de définir une propriété comme étant la clé primaire de l'entité.
 *
 * Cet attribut est utilisé pour marquer une propriété spécifique d'une classe PHP comme
 * étant l'identifiant unique (clé primaire) de l'entité correspondante dans la base de données.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class PrimaryKey
{
    /**
     * Constructeur de l'attribut PrimaryKey.
     *
     * @param bool $autoIncrement Indique si la clé primaire est auto-incrémentée (par défaut true).
     */
    public function __construct(
        private bool $autoIncrement = true
    ) {
    }

    /**
     * Vérifie si la clé primaire est auto-incrémentée.
     *
     * @return bool Vrai si auto-incrémentée, faux sinon.
     */
    public function isAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    /**
     * Définit si la clé primaire est auto-incrémentée.
     *
     * @param bool $autoIncrement Vrai pour auto-incrémenter.
     * @return self
     */
    public function setAutoIncrement(bool $autoIncrement): self
    {
        $this->autoIncrement = $autoIncrement;
        return $this;
    }
}
