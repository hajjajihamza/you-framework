<?php

namespace YouHttpFoundation;

/**
 * ResponseInterface définit le contrat pour une réponse HTTP.
 */
interface ResponseInterface
{
    /**
     * Envoie les en-têtes HTTP et le contenu.
     *
     * @return static
     */
    public function send(): static;

    /**
     * Définit le contenu de la réponse.
     *
     * @param string|null $content Le contenu de la réponse
     * @return static
     */
    public function setContent(?string $content): static;

    /**
     * Récupère le contenu de la réponse.
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Définit le code de statut HTTP.
     *
     * @param int   $code Le code de statut
     * @param mixed $text Le texte associé au statut
     * @return static
     */
    public function setStatusCode(int $code, mixed $text = null): static;

    /**
     * Récupère le code de statut.
     *
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * Définit la version du protocole HTTP.
     *
     * @param string $version La version du protocole (ex: "1.0" ou "1.1")
     * @return static
     */
    public function setProtocolVersion(string $version): static;

    /**
     * Récupère la version du protocole HTTP.
     *
     * @return string
     */
    public function getProtocolVersion(): string;
}