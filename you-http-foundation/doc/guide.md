# Documentation de la Bibliothèque YouHttpFoundation

**YouHttpFoundation** est un composant qui permet de gérer les requêtes et les réponses HTTP de manière orientée objet, en encapsulant les superglobales de PHP.

## Fonctionnalités Principales

- **Abstraction de la Requête** : La classe `Request` encapsule `$_GET`, `$_POST`, `$_COOKIE`, `$_FILES` et `$_SERVER`.
- **Abstraction de la Réponse** : La classe `Response` facilite la création de réponses HTTP avec contenu, en-têtes et codes de statut.
- **Gestion des Paramètres** : La classe `ParameterBag` offre une interface fluide pour manipuler les tableaux de données.
- **Interface Standard** : Utilisation de `ResponseInterface` pour garantir la compatibilité.

## Installation

Assurez-vous que votre projet utilise PHP 8.1 ou supérieur.

```bash
composer require you-framework/you-http-foundation
```

## La Classe Request

La classe `YouHttpFoundation\Request` représente une requête HTTP entrante.

### Création d'une Requête

La méthode recommandée pour créer une requête est d'utiliser `createFromGlobals()`, qui capture automatiquement l'environnement PHP actuel.

```php
use YouHttpFoundation\Request;

$request = Request::createFromGlobals();
```

### Accéder aux Données

La classe `Request` expose plusieurs propriétés publiques qui sont des instances de `YouHttpFoundation\ParameterBag` :

- `$request->query` : Correspond à `$_GET` (paramètres d'URL).
- `$request->request` : Correspond à `$_POST` (paramètres de corps).
- `$request->cookies` : Correspond à `$_COOKIE`.
- `$request->files` : Correspond à `$_FILES`.
- `$request->server` : Correspond à `$_SERVER`.

**Exemple d'utilisation :**

```php
// Récupérer un paramètre GET 'id', avec une valeur par défaut null
$id = $request->query->get('id');

// Récupérer un paramètre POST 'username'
$username = $request->request->get('username', 'Anonyme');

// Vérifier si un cookie existe
if ($request->cookies->has('session_id')) {
    // ...
}
```

### Méthodes Utiles

- `getPath()` : Retourne le chemin de l'URI nettoyé (ex: `/blog/article`).
- `getMethod()` : Retourne la méthode HTTP (ex: `GET`, `POST`).
- `isMethod(string $method)` : Vérifie si la méthode correspond (ex: `$request->isMethod('POST')`).

Note : `createFromGlobals()` gère automatiquement le décodage du JSON si le header `Content-Type` est `application/json` pour les méthodes POST, PUT ou PATCH.

## La Classe Response

La classe `YouHttpFoundation\Response` permet de construire la réponse à envoyer au client.

### Création d'une Réponse

```php
use YouHttpFoundation\Response;

$response = new Response(
    'Contenu de la page (HTML, JSON, etc.)',
    200, // Code de statut (défaut: 200)
    ['Content-Type' => 'text/html'] // En-têtes (défaut: [])
);
```

### Manipulation de la Réponse

Vous pouvez modifier la réponse après sa création :

```php
$response->setContent('Nouveau contenu');
$response->setStatusCode(404);
$response->headers->set('X-Custom-Header', 'Value');
```

### Envoyer la Réponse

Pour envoyer les en-têtes et le contenu au navigateur, utilisez la méthode `send()`.

```php
$response->send();
```

## La Classe ParameterBag

C'est un conteneur utilitaire utilisé par `Request` et `Response` pour gérer les collections de données.

**Méthodes principales :**

- `all()` : Retourne tous les paramètres.
- `keys()` : Retourne les clés.
- `get(string $key, mixed $default = null)` : Récupère une valeur.
- `set(string $key, mixed $value)` : Définit une valeur.
- `has(string $key)` : Vérifie l'existence d'une clé.
- `remove(string $key)` : Supprime un paramètre.
- `count()` : Retourne le nombre de paramètres.
- `replace(array $parameters)` : Remplace tous les paramètres.
- `add(array $parameters)` : Ajoute des paramètres (merge).

## Structure des Dossiers

```text
you-http-foundation/
├── src/
│   ├── ParameterBag.php       # Gestionnaire de clés/valeurs
│   ├── Request.php            # Objet Requête
│   ├── Response.php           # Objet Réponse
│   └── ResponseInterface.php  # Contrat pour les réponses
└── doc/
    └── guide.md               # Ce fichier de documentation
```
