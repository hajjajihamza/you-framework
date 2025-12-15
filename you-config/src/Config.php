<?php

namespace YouConfig;

use Exception;
use RuntimeException;

class Config
{
    /**
     * Les éléments de configuration chargés.
     *
     * @var array
     */
    private array $items = [];

    /**
     * Le chemin vers le dossier contenant les fichiers de configuration.
     *
     * @var string
     */
    private string $configPath;

    /**
     * Constructeur de la classe Config.
     *
     * @param string $configPath Le chemin absolu vers le dossier de configuration.
     *
     * @throws RuntimeException Si le dossier de configuration n'existe pas.
     */
    public function __construct(string $configPath)
    {
        if (!file_exists($configPath)) {
            throw new RuntimeException('Config path does not exist: ' . $configPath);
        }

        $this->configPath = rtrim($configPath, '/\\');
        $this->load();
    }

    /**
     * Charge tous les fichiers de configuration PHP présents dans le dossier défini.
     *
     * Chaque fichier est chargé et son contenu (qui doit être un tableau) est stocké
     * sous une clé correspondant au nom du fichier (sans l'extension).
     *
     * @return void
     */
    private function load(): void
    {
        // 1. Load base configuration files
        $files = glob($this->configPath . '/*.php');
        foreach ($files as $file) {
            $filename = basename($file);

            $key = basename($filename, '.php');
            $data = require $file;

            if (is_array($data)) {
                $this->items[$key] = $data;
            }
        }
    }

    /**
     * Valide la présence des clés de configuration requises.
     *
     * @param array $requiredKeys Liste des clés qui doivent impérativement exister.
     *
     * @return void
     * @throws RuntimeException Si une clé requise est manquante.
     */
    public function validate(array $requiredKeys): void
    {
        foreach ($requiredKeys as $key) {
            if ($this->get($key) === null) {
                throw new RuntimeException(sprintf('Config key "%s" is required but missing.', $key));
            }
        }
    }

    /**
     * Récupère une valeur de configuration.
     *
     * Supporte la notation par points (dot notation) pour accéder aux tableaux imbriqués.
     * Par exemple: 'database.host'.
     *
     * @param string $key La clé de configuration à récupérer.
     * @param mixed $default La valeur par défaut à retourner si la clé n'existe pas.
     *
     * @return mixed La valeur de la configuration ou la valeur par défaut.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $array = $this->items;

        // Check if the key exists
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        // Check if the key exists in a nested array
        foreach (explode('.', $key) as $segment) {
            // If the segment exists, continue
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        // Return the value
        return $array;
    }

    /**
     * Récupère la valeur entière d'une clé spécifique
     *
     * @param string $key La clé dont on veut obtenir la valeur
     * @param int $default La valeur par défaut, 0 par défaut
     * @return int Retourne la valeur convertie en type entier
     */
    public function getInt(string $key, int $default = 0): int
    {
        return (int)$this->get($key, $default);
    }


    /**
     * Récupère la valeur booléenne d'une clé spécifique
     *
     * @param string $key La clé dont on veut obtenir la valeur
     * @param bool $default La valeur par défaut, false par défaut
     * @return bool Retourne la valeur convertie en type booléen
     */
    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);
        if (is_string($value)) {
            $value = strtolower($value);
            return in_array($value, ['true', '1', 'yes', 'on'], true);
        }
        return (bool)$value;
    }


    /**
     * Retourne tous les éléments de configuration chargés.
     *
     * @return array Le tableau complet des configurations.
     */
    public function all(): array
    {
        return $this->items;
    }
}
