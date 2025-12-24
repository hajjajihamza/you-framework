<?php

namespace YouOrm\Attribute;

use Attribute;

/**
 * Attribut permettant de définir la table de base de données associée à une entité.
 *
 * Cet attribut est utilisé pour mapper une classe PHP à une table spécifique
 * dans la base de données. Il permet également de configurer des options
 * supplémentaires comme le schéma ou la classe de dépôt (repository).
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    /**
     * Constructeur de l'attribut Table.
     *
     * @param string $name Le nom de la table dans la base de données.
     * @param string|null $schema Le nom du schéma de la base de données (par défaut null).
     * @param string|null $repositoryClass La classe de repository personnalisée à utiliser pour cette entité (par défaut null).
     * @param bool $readOnly Indique si la table doit être considérée comme étant en lecture seule (par défaut false).
     */
    public function __construct(
        private string $name,
        private ?string $schema = null,
        private ?string $repositoryClass = null,
        private bool $readOnly = false
    ) {
    }

    /**
     * Récupère le nom de la table.
     *
     * @return string Le nom de la table.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Définit le nom de la table.
     *
     * @param string $name Le nom de la table.
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Récupère le nom du schéma.
     *
     * @return string|null Le nom du schéma ou null si non défini.
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }

    /**
     * Définit le nom du schéma.
     *
     * @param string|null $schema Le nom du schéma.
     * @return self
     */
    public function setSchema(?string $schema): self
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * Récupère la classe de repository associée.
     *
     * @return string|null La classe de repository ou null si non définie.
     */
    public function getRepositoryClass(): ?string
    {
        return $this->repositoryClass;
    }

    /**
     * Définit la classe de repository associée.
     *
     * @param string|null $repositoryClass La classe de repository.
     * @return self
     */
    public function setRepositoryClass(?string $repositoryClass): self
    {
        $this->repositoryClass = $repositoryClass;
        return $this;
    }

    /**
     * Vérifie si la table est en lecture seule.
     *
     * @return bool Vrai si la table est en lecture seule, faux sinon.
     */
    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    /**
     * Définit si la table est en lecture seule.
     *
     * @param bool $readOnly Vrai pour lecture seule, faux pour lecture/écriture.
     * @return self
     */
    public function setReadOnly(bool $readOnly): self
    {
        $this->readOnly = $readOnly;
        return $this;
    }
}
