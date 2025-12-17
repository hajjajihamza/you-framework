# Documentation de la Bibliothèque YouKernel

**YouKernel** est le cœur du framework. Il orchestre l'initialisation de l'application, l'injection de dépendances et le cycle de vie des requêtes HTTP et des commandes Console.

## Fonctionnalités Principales

- **Classe Application** : Point d'entrée unique pour démarrer le projet.
- **Conteneur de Services** : Gestionnaire de dépendances avec auto-wiring (résolution automatique).
- **Bootstrap** : Chargement automatique de l'environnement, de la configuration et des routes.
- **Micro-Kerneaux** : Séparation claire entre le cycle de vie HTTP (`HttpKernel`) et Console (`YouConsoleKernel`).

## Installation

```bash
composer require you-framework/you-kernel
```

## Structure d'un Projet

Pour utiliser YouKernel, votre projet doit suivre une structure standard :

```text
my-project/
├── config/              # Fichiers de configuration
├── public/
│   └── index.php        # Point d'entrée HTTP
├── src/
│   ├── Controller/      # Contrôleurs
│   └── Entity/          # Entités
└── you                  # Point d'entrée Console
```

## Démarrer l'Application

La classe `YouKernel\Application` est la façade principale.

### Point d'entrée HTTP (`public/index.php`)

```php
<?php

use YouKernel\Application;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Initialise l'application avec la racine du projet
$app = new Application(dirname(__DIR__));

// Lance le traitement HTTP
$app->runHttp();
```

### Point d'entrée Console (`you`)

```php
#!/usr/bin/env php
<?php

use YouKernel\Application;

require __DIR__ . '/vendor/autoload.php';

$app = new Application(__DIR__);

// Lance le traitement Console
$app->runConsole($argv);
```

## Le Conteneur de Services

YouKernel intègre un conteneur d'injection de dépendances (DI) simple et puissant.

### Auto-wiring

Le conteneur est capable de résoudre automatiquement les dépendances de vos classes (Contrôleurs, Services, Commandes) en analysant leurs constructeurs via la réflexion PHP.

```php
namespace App\Service;

class LoggerService {
    public function log(string $msg) { /* ... */ }
}

namespace App\Controller;

class HomeController {
    // LoggerService sera automatiquement injecté sans configuration
    public function __construct(private LoggerService $logger) {}
}
```

### Configuration Manuelle

Si nécessaire (interfaces, scalaires, librairies tierces), vous pouvez configurer le conteneur via les fichiers de configuration (orchestré par `ContainerBootstrapper`, non détaillé ici mais extensible).

## Cycle de Vie HTTP

1.  **Bootstrap** : Chargement des variables d'env, de la config et du conteneur.
2.  **Routing** : Le `HttpKernel` utilise `YouRoute` pour trouver la route correspondante.
3.  **Résolution** : Le `ControllerResolver` instancie le contrôleur et injecte les dépendances.
4.  **Exécution** : L'action du contrôleur est appelée.
5.  **Réponse** : Le résultat est transformé en `Response` et envoyé au client.

## Structure des Dossiers du Noyau

```text
you-kernel/
├── src/
│   ├── Application.php    # Façade principale
│   ├── Component/
│   │   ├── Container/     # Conteneur DI
│   │   ├── Http/          # Kernel HTTP
│   │   └── Controller/    # Logique des contrôleurs
│   └── Bootstrap/         # Classes d'initialisation
└── doc/
    └── guide.md           # Ce fichier de documentation
```
