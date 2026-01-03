<?php

namespace YouOrm\Discovery;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;
use YouOrm\Schema\Attribute\Table;

/**
 * Class EntityDiscovery
 * Responsible for discovering entity classes with the #[Table] attribute.
 */
class EntityDiscovery
{
    /**
     * Discover entities in the given paths.
     *
     * @param string $directory Répertoire à scanner.
     * @return array<string> List of fully qualified class names of discovery entities.
     */
    public function discover(string $directory): array
    {
        $entities = [];

        if (!is_dir($directory)) {
            return $entities;
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

            if ($className) {
                $entities[] = $className;
            }
        }

        return $entities;
    }
}
