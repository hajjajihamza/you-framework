<?php

if (!function_exists('env')) {
    /**
     * Récupère la valeur d'une variable d'environnement ou renvoie une valeur par défaut.
     *
     * @param string $key La clé de la variable d'environnement.
     * @param mixed $default La valeur par défaut à retourner si la clé n'existe pas.
     * @return mixed La valeur de la variable d'environnement ou la valeur par défaut.
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key) ?: $_ENV[$key] ?? $default;

        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        if ($value === 'null') {
            return null;
        }

        return $value;
    }
}

if (!function_exists('fqcn')) {
    /**
     * Récupère le nom complet de la classe (Fully Qualified Class Name) à partir d'un fichier PHP.
     * Supporte les classes, interfaces, traits et énumérations.
     *
     * @param string $filePath Le chemin absolu vers le fichier PHP.
     * @return string|null Le FQCN ou null si non trouvé.
     */
    function fqcn(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        $namespace = '';
        if (preg_match('/^\s*namespace\s+([a-zA-Z0-9_\\\\]+)\s*;/m', $content, $namespaceMatches)) {
            $namespace = $namespaceMatches[1] . '\\';
        }

        // Match class/interface/trait/enum name
        if (!preg_match('/^\s*(?:abstract\s+|final\s+|readonly\s+)*(?:class|interface|trait|enum)\s+(\w+)/m', $content, $classMatches)) {
            return null;
        }

        return $namespace . $classMatches[1];
    }
}

if (!function_exists('discover_classes')) {
    /**
     * Découvre tous les class PHP dans un répertoire et ses sous-répertoires.
     *
     * @param string $directory Le chemin absolu vers le répertoire.
     * @return array Un tableau contenant les FQCN des classes.
     */
    function discover_classes(string $directory): array
    {
        $classes = [];

        if (!is_dir($directory)) {
            return $classes;
        }

        /** @var SplFileInfo[] $iterator */
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );


        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = fqcn($file->getPathname());

            if ($className && class_exists($className)) {
                $classes[] = $className;
            }
        }

        return $classes;
    }
}

if (!function_exists('discover_files')) {
    /**
     * Découvre tous les fichiers dans un répertoire et ses sous-répertoires.
     *
     * @param string $directory Le chemin absolu vers le répertoire.
     * @param string|null $extension L'extension de fichier à filtrer (ex: 'php').
     * @return array Un tableau contenant les chemins absolus des fichiers.
     */
    function discover_files(string $directory, ?string $extension = null): array
    {
        $files = [];

        if (!is_dir($directory)) {
            return $files;
        }

        /** @var SplFileInfo[] $iterator */
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            if ($extension && $file->getExtension() !== $extension) {
                continue;
            }

            $files[] = $file->getPathname();
        }

        return $files;
    }
}

if (!function_exists('base_path')) {
    /**
     * Récupère le chemin absolu vers la racine du projet.
     *
     * @param string $path Un chemin relatif à ajouter à la racine.
     * @return string Le chemin absolu complet.
     */
    function base_path(string $path = ''): string
    {
        $base = dirname(__DIR__);
        return $path ? $base . DIRECTORY_SEPARATOR . $path : $base;
    }
}