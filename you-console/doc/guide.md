# Documentation de la Bibliothèque YouConsole

**YouConsole** est un composant permettant de créer et de gérer des applications en ligne de commande (CLI). Il fournit une structure robuste pour définir des commandes, gérer les entrées (arguments, options) et formater les sorties.

## Fonctionnalités Principales

- **Système de Commandes** : Créez des classes de commandes isolées et réutilisables.
- **Gestion des Entrées** : Parsing automatique des arguments et options (avec ou sans valeurs).
- **Gestion des Sorties** : Affichage coloré et formaté (info, error, success, etc.).
- **Auto-découverte** : Chargement automatique des commandes depuis un dossier spécifié.

## Installation

```bash
composer require you-framework/you-console
```

## Créer une Commande

Pour créer une nouvelle commande, vous devez créer une classe qui étend `YouConsole\Command\AbstractCommand`.

Vous devez implémenter deux méthodes :
1.  `configure()` : Définition du nom, de la description, des arguments et des options.
2.  `execute(Input $input, Output $output)` : La logique métier de votre commande.

### Exemple de Commande

```php
<?php

namespace App\Command;

use YouConsole\Command\AbstractCommand;
use YouConsole\Input\Input;
use YouConsole\Output\Output;

class GreetCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            // Le nom de la commande (ex: php you app:greet)
            ->setName('app:greet')
            
            // Une courte description
            ->setDescription('Salue un utilisateur.')
            
            // Un argument obligatoire "name"
            ->addArgument('name', required: true, description: 'Le nom de la personne à saluer')
            
            // Une option "yell" (booléen, pas de valeur attendue) avec raccourci -Y
            ->addOption('yell', shortcut: 'Y', description: 'Si défini, le message sera en majuscules');
    }

    protected function execute(Input $input, Output $output): int
    {
        // Récupérer l'argument "name"
        $name = $input->getArgument('name');
        
        $message = "Bonjour, $name !";

        // Vérifier si l'option "yell" est activée
        if ($input->getOption('yell')) {
            $message = strtoupper($message);
        }

        // Afficher le message en vert (succès)
        $output->success($message);

        // Retourner le code de succès (0)
        return AbstractCommand::STATUS_SUCCESS;
    }
}
```

## Utiliser YouConsoleKernel

Le kernel est le point d'entrée de votre application console. Il se charge de découvrir les commandes et d'exécuter celle demandée.

```php
#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use YouConsole\YouConsoleKernel;
use YouKernel\Component\Container\ContainerBuilder;

// ... (Initialisation du conteneur si nécessaire)

$kernel = new YouConsoleKernel($container);

// Exécuter l'application
$exitCode = $kernel->run();

exit($exitCode);
```

## Entrées (Input)

La classe `Input` permet d'accéder aux données passées en ligne de commande.

- `$input->getArgument('nom')` : Récupère la valeur d'un argument.
- `$input->getOption('nom')` : Récupère la valeur d'une option.
- `$input->hasOption('nom')` : Vérifie si une option a été passée.

## Sorties (Output)

La classe `Output` permet d'écrire dans la console avec du style.

- `$output->write($message)` : Écrit sans retour à la ligne.
- `$output->writeln($message)` : Écrit avec retour à la ligne.
- `$output->success($message)` : Texte en vert.
- `$output->error($message)` : Texte blanc sur fond rouge.
- `$output->info($message)` : Texte en cyan.
- `$output->comment($message)` : Texte en jaune.
- `$output->question($message)` : Texte noir sur fond cyan.

## Structure des Dossiers

```text
you-console/
├── src/
│   ├── Command/       # Classes de base pour les commandes
│   ├── Input/         # Gestion parsing arguments/options
│   ├── Output/        # Gestion affichage et styles
│   └── YouConsoleKernel.php # Point d'entrée
└── doc/
    └── guide.md       # Ce fichier de documentation
```
