<?php

namespace YouMake\Command\Generator;

use YouConfig\Config;
use YouConsole\Input\Input;
use YouConsole\Output\Output;

/**
 * Commande pour générer un contrôleur.
 */
class ControllerMakeCommand extends AbstractGeneratorCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:controller')
            ->setDescription('Génère un nouveau contrôleur')
            ->addArgument('name', true, 'Le nom du contrôleur')
            ->addOption('no-template', null, false, 'Génère un contrôleur sans template')
        ;
    }

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../Resources/stubs/controller.stub';
    }

    /**
     * @return string
     */
    protected function getTemplateStubPath(): string
    {
        return __DIR__ . '/../../Resources/stubs/template.stub';
    }

    /**
     * @param string $className
     * @return array<string, string>
     */
    protected function getReplacements(string $className): array
    {
        $replacements = parent::getReplacements($className);
        $replacements['{{ template_name }}'] = $this->getTemplateName($className);
        $replacements['{{ title }}'] = $this->getClassName($className);
        $replacements['{{ path }}'] = $this->getPath($className);
        $replacements['{{ path_name }}'] = $this->getPathName($className);

        return $replacements;
    }

    /**
     * @param Input $input
     * @param Output $output
     * @return int
     * @throws \ReflectionException
     */
    protected function execute(Input $input, Output $output): int
    {
        $className = $input->getArgument('name');

        // Générer le contrôleur via la classe parente
        $status = parent::execute($input, $output);

        if ($status !== self::STATUS_SUCCESS) {
            return $status;
        }

        // Générer le template si l'option no-template n'est pas présente
        if (!$input->getOption('no-template')) {
            $templatePath = $this->getDestinationPathTemplate($className);

            if (file_exists($templatePath)) {
                $output->comment("Le template existe déjà : $templatePath");
                return self::STATUS_SUCCESS;
            }

            $this->makeDirectory($templatePath);

            $stubPath = $this->getTemplateStubPath();
            $stub = file_get_contents($stubPath);

            if ($stub === false) {
                $output->error("Template stub introuvable : $stubPath");
                return self::STATUS_ERROR;
            }

            $replacements = $this->getReplacements($className);
            $content = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $stub
            );

            if (file_put_contents($templatePath, $content) === false) {
                $output->error("Impossible d'écrire le template : $templatePath");
                return self::STATUS_ERROR;
            }

            $output->success("Template généré avec succès : $templatePath");
        }

        return self::STATUS_SUCCESS;
    }

    /**
     * @param string $className
     * @return string
     */
    private function getTemplateName(string $className): string
    {
        $className = str_replace(['Controller', '\\'], ['', '/'], $className);
        return strtolower(ltrim($className, '/')) . '.html.twig';
    }

    /**
     * @param string $className
     * @return string
     * @throws \ReflectionException
     */
    protected function getDestinationPath(string $className): string
    {
        $config = $this->container->get(Config::class);
        $projectDir = $this->container->get('project_dir');

        $controllersPath = $projectDir . '/' . ltrim($config->get('app.routes.resource', 'src/Controller'), '/');
        $className = str_replace('\\', '/', $className);

        return sprintf('%s/%s.php', $controllersPath, ltrim($className, '/'));
    }

    /**
     * @param string $className
     * @return string
     * @throws \ReflectionException
     */
    protected function getDestinationPathTemplate(string $className): string
    {
        $config = $this->container->get(Config::class);
        $projectDir = $this->container->get('project_dir');

        $templatesPath = $projectDir . '/' . ltrim($config->get('app.twig.path', 'templates'), '/');

        return sprintf('%s/%s', $templatesPath, $this->getTemplateName($className));
    }

    /**
     * Génère le chemin de route basé sur le nom du contrôleur.
     * Exemple: UserProfileController -> /user/profile
     *
     * @param string $className
     * @return string
     */
    protected function getPath(string $className): string
    {
        $className = str_replace(['Controller', '\\'], ['', '/'], $className);
        $path = strtolower(ltrim($className, '/'));

        return '/' . $path;
    }

    /**
     * Génère le nom de route basé sur le nom du contrôleur.
     * Exemple: UserProfileController -> user_profile
     *
     * @param string $className
     * @return string
     */
    protected function getPathName(string $className): string
    {
        $className = str_replace(['Controller', '\\'], ['', '_'], $className);
        $pathName = strtolower(ltrim($className, '_'));

        return str_replace('/', '_', $pathName);
    }

    /**
     * @param string $className
     * @return string
     */
    protected function getDefaultNamespace(string $className): string
    {
        $namespace = 'App\\Controller';
        $parts = explode('\\', str_replace('/', '\\', $className));
        array_pop($parts);

        if (!empty($parts)) {
            $namespace .= '\\' . implode('\\', $parts);
        }

        return $namespace;
    }
}
