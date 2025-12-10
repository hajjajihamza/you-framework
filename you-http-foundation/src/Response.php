<?php

namespace YouHttpFoundation;

use InvalidArgumentException;

/**
 * Response représente une réponse HTTP.
 */
class Response implements ResponseInterface
{
    /**
     * @var ParameterBag
     */
    public ParameterBag $headers;

    /**
     * @var string
     */
    protected string $content;

    /**
     * @var string
     */
    protected string $version;

    /**
     * @var int
     */
    protected int $statusCode;

    /**
     * @var string
     */
    protected string $statusText;

    /**
     * Codes de statut HTTP standards.
     *
     * @var array
     */
    public static array $statusTexts = [
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        404 => 'Not Found',
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    ];

    /**
     * Constructeur.
     *
     * @param string|null $content Le contenu de la réponse
     * @param int         $status  Le code de statut HTTP (200 par défaut)
     * @param array       $headers Un tableau d'en-têtes HTTP
     */
    public function __construct(?string $content = '', int $status = 200, array $headers = [])
    {
        $this->headers = new ParameterBag($headers);
        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setProtocolVersion('1.0');
    }

    /**
     * Envoie les en-têtes HTTP et le contenu.
     *
     * @return $this
     */
    public function send(): static
    {
        $this->sendHeaders();
        $this->sendContent();

        // Ferme la requête
        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        return $this;
    }

    /**
     * Envoie les en-têtes HTTP.
     *
     * @return $this
     */
    public function sendHeaders(): static
    {
        // Les en-têtes ont déjà été envoyés ?
        if (headers_sent()) {
            return $this;
        }

        foreach ($this->headers->all() as $name => $values) {
            // Remplace le Content-Type si nécessaire
            $replace = 0 === strcasecmp($name, 'Content-Type');

            // Envoi des en-têtes
            foreach ($values as $value) {
                header($name . ': ' . $value, $replace, $this->statusCode);
            }
        }

        // Envoi du code de statut
        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);

        return $this;
    }

    /**
     * Envoie le contenu pour le corps de la réponse.
     *
     * @return $this
     */
    public function sendContent(): static
    {
        echo $this->content;

        return $this;
    }

    /**
     * Définit le contenu de la réponse.
     *
     * @param string|null $content
     * @return $this
     */
    public function setContent(?string $content): static
    {
        $this->content = (string) $content;

        return $this;
    }

    /**
     * Récupère le contenu de la réponse.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Définit le code de statut HTTP.
     *
     * @param int   $code Le code de statut
     * @param mixed $text Le texte associé au statut
     *
     * @return $this
     *
     * @throws InvalidArgumentException Si le code de statut n'est pas valide
     */
    public function setStatusCode(int $code, mixed $text = null): static
    {
        $this->statusCode = $code;
        if ($this->isInvalid()) {
            throw new InvalidArgumentException(sprintf('Le code de statut HTTP "%s" n\'est pas valide.', $code));
        }

        if (null === $text) {
            $this->statusText = self::$statusTexts[$code] ?? 'unknown status';
        } else {
            $this->statusText = $text;
        }

        return $this;
    }

    /**
     * Récupère le code de statut.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Définit la version du protocole HTTP (1.0 ou 1.1).
     *
     * @param string $version
     * @return $this
     */
    public function setProtocolVersion(string $version): static
    {
        if (!\in_array($version, ['1.0', '1.1', '2.0'])) {
            throw new InvalidArgumentException(sprintf('La version du protocole HTTP "%s" n\'est pas valide.', $version));
        }

        $this->version = $version;

        return $this;
    }

    /**
     * Récupère la version du protocole HTTP.
     *
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->version;
    }

    // --- Méthodes de vérification de statut ---

    public function isInvalid(): bool
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    public function isInformational(): bool
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function isRedirection(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    public function isOk(): bool
    {
        return 200 === $this->statusCode;
    }

    public function isForbidden(): bool
    {
        return 403 === $this->statusCode;
    }

    public function isNotFound(): bool
    {
        return 404 === $this->statusCode;
    }

    public function isEmpty(): bool
    {
        return \in_array($this->statusCode, [204, 304]);
    }
}
