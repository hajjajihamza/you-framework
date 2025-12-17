# Documentation de la Bibliothèque YouRoute

**YouRoute** est un composant de routing léger et performant pour PHP, basé sur les attributs (annotations natives de PHP 8). Il permet de mapper des URLs à des méthodes de contrôleurs de manière simple et intuitive.

## Fonctionnalités Principales

- **Définition de routes via Attributs** : Utilisez `#[Route]` directement sur vos classes et méthodes.
- **Support des méthodes HTTP** : GET, POST, PUT, DELETE, etc.
- **Paramètres dynamiques** : Capture de segments d'URL (ex: `/user/{id}`).
- **Préfixes de groupe** : Définition de préfixes de route au niveau de la classe contrôleur.
- **Génération d'URLs** : Construction facile de liens vers vos routes nommées.
- **Scan automatique** : Découverte automatique des contrôleurs dans un répertoire donné.

## Installation

Assurez-vous que votre projet utilise PHP 8.1 ou supérieur.

(Si la bibliothèque est installée via Composer, incluez la commande ici. Sinon, assurez-vous que les namespaces sont bien chargés par votre autoloader).

## Initialisation

Le point d'entrée principal est la classe `YouRoute\YouRouteKernal`. Vous devez l'instancier en lui fournissant le chemin absolu vers le répertoire contenant vos contrôleurs.

```php
use YouRoute\YouRouteKernal;

// Définir le chemin vers vos contrôleurs
$controllersPath = __DIR__ . '/src/Controller';

// Initialiser le kernel de routage
$youRoute = new YouRouteKernal($controllersPath);
```

## Définir des Routes

### L'Attribut `#[Route]`

L'attribut `YouRoute\Attribute\Route` est utilisé pour déclarer une route. Il accepte trois arguments :

1.  `path` (string) : Le chemin de l'URL (obligatoire). Peut contenir des paramètres entre accolades `{}`.
2.  `name` (string) : Un nom unique pour la route (optionnel, mais recommandé pour la génération d'URL).
3.  `methods` (string|array) : La méthode HTTP ou la liste des méthodes autorisées (défaut : "GET").

### Exemple de Contrôleur

Les contrôleurs doivent être des classes instanciables et leurs méthodes d'action doivent être **publiques** et retourner une instance de `YouHttpFoundation\ResponseInterface`.

```php
<?php

namespace App\Controller;

use YouRoute\Attribute\Route;
use YouHttpFoundation\Response;
use YouHttpFoundation\ResponseInterface;

class HomeController
{
    #[Route('/', name: 'home')]
    public function index(): ResponseInterface
    {
        return new Response('Bienvenue sur la page d\'accueil !');
    }

    // Route avec paramètre et méthodes multiples
    #[Route('/user/{id}', name: 'user_show', methods: ['GET'])]
    public function show(int $id): ResponseInterface
    {
        return new Response("Affichage de l'utilisateur numéro : $id");
    }
}
```

### Préfixer les Routes (Groupes)

Vous pouvez placer l'attribut `#[Route]` sur la **classe** elle-même pour définir un préfixe commun à toutes les routes de cette classe.

```php
#[Route('/admin', name: 'admin.')]
class AdminController
{
    // L'URL sera /admin/dashboard et le nom 'admin.dashboard'
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): ResponseInterface
    {
        return new Response('Tableau de bord Admin');
    }
}
```

## Dispatcher la Requête

Une fois le kernel initialisé, vous pouvez dispatcher la requête courante pour trouver la route correspondante.

```php
// Récupérer la méthode et l'URI de la requête (par exemple depuis $_SERVER)
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Dispatcher
$result = $youRoute->dispatch($method, $uri);

if ($result) {
    // Route trouvée
    $route = $result['route']; // Instance de YouRoute\Attribute\Route
    $params = $result['params']; // Paramètres extraits de l'URL (ex: ['id' => 45])
    
    // Récupérer l'action (Contrôleur et Méthode)
    [$controllerClass, $actionMethod] = $route->getAction();
    
    // Instancier le contrôleur et appeler la méthode
    $controller = new $controllerClass();
    
    // Vous pouvez passer les paramètres à la méthode si nécessaire
    // Note: YouRoute ne fait pas l'injection de dépendances automatique pour l'instant, 
    // l'appel de la méthode est à votre charge ou celle de votre framework.
    $response = call_user_func_array([$controller, $actionMethod], $params);
    
    // Envoyer la réponse
    $response->send();
    
} else {
    // Route non trouvée (404)
    http_response_code(404);
    echo "Page non trouvée";
}
```

## Générer des URLs

Vous ne devez jamais écrire des URLs en dur dans votre code. Utilisez plutôt le générateur d'URL avec le nom de la route.

```php
try {
    // Génère /user/42
    $url = $youRoute->generateUrl('user_show', ['id' => 42]);
    echo "<a href='$url'>Voir le profil</a>";

    // Les paramètres non présents dans le chemin sont ajoutés en Query String
    // Génère /user/42?ref=newsletter
    $urlWithQuery = $youRoute->generateUrl('user_show', ['id' => 42, 'ref' => 'newsletter']);

} catch (\RuntimeException $e) {
    // La route demandée n'existe pas
}
```

## Structure des Dossiers

```text
you-route/
├── src/
│   ├── Attribute/     # Définition de l'attribut #[Route]
│   ├── Router/        # Logique de collection, résolution et dispatch
│   └── YouRouteKernal.php # Point d'entrée principal
└── doc/
    └── guide.md       # Ce fichier de documentation
```
