# Documentation Backend - Paroisses

## Architecture

L'application suit une architecture simple et performante :
- **Form Requests** pour la validation
- **Policies** pour l'autorisation
- **Actions** pour la logique métier complexe
- **API Resources** pour contrôler les données exposées
- **Observers** pour l'invalidation automatique du cache
- **Scopes** pour des requêtes réutilisables

**Pas de Repository Pattern** - Eloquent est utilisé directement pour la simplicité.

## Form Requests

Toutes les validations sont centralisées dans des Form Requests.

### Structure
```
app/Http/Requests/
├── Admin/
│   ├── Annonce/
│   │   ├── StoreAnnonceRequest.php
│   │   └── UpdateAnnonceRequest.php
│   ├── Horaire/
│   │   ├── StoreHoraireRequest.php
│   │   └── UpdateHoraireRequest.php
│   └── Paroisse/
│       └── UpdateParoisseRequest.php
└── Super/
    └── StoreParoisseRequest.php
```

### Utilisation
```php
public function store(StoreAnnonceRequest $request)
{
    // $request->validated() contient les données validées
    $data = $request->validated();
    
    // ...
}
```

### Messages en français
Tous les messages de validation sont en français, définis dans chaque Request.

## Policies

Les autorisations sont gérées par des Policies Laravel.

### Policies disponibles
- **AnnoncePolicy** : gestion des annonces
- **HorairePolicy** : gestion des horaires  
- **ParoissePolicy** : gestion des paroisses

### Hiérarchie des permissions
1. **super_admin** : accès total, gère toutes les paroisses
2. **admin** : gère sa propre paroisse et ses utilisateurs
3. **secretariat** : lecture/écriture dans sa paroisse

### Utilisation
```php
// Dans un contrôleur
$this->authorize('update', $annonce);

// Dans une vue Blade/Inertia
@can('delete', $annonce)
    <!-- Bouton supprimer -->
@endcan
```

### Réponses 404 au lieu de 403
Pour ne pas révéler l'existence de ressources d'autres paroisses, retourner 404:
```php
$annonce = Annonce::findOrFail($id); // 404 si pas trouvé
$this->authorize('view', $annonce);  // 403 seulement si trouvé mais pas autorisé
```

## Actions

Classes à usage unique pour la logique métier complexe.

### Actions disponibles
- **CreateParoisseWithAdmin** : crée une paroisse avec son admin (transaction)
- **PublishAnnonce** : publie une annonce immédiatement

### Création d'une nouvelle Action
```php
<?php

namespace App\Actions\MonModule;

class MonAction
{
    public function execute(array $data): Model
    {
        // Logique métier ici
        // Utiliser DB::transaction() si nécessaire
        
        return $result;
    }
}
```

### Utilisation
```php
use App\Actions\Paroisse\CreateParoisseWithAdmin;

public function store(StoreParoisseRequest $request)
{
    $paroisse = app(CreateParoisseWithAdmin::class)->execute($request->validated());
    
    return redirect()->route('super.paroisses.index');
}
```

## API Resources

Contrôlent exactement quelles données sont exposées côté client.

### Resources disponibles
- **PublicParoisseResource** : données publiques (pas d'IDs, pas d'emails internes)
- **AnnonceResource** : annonces avec métadonnées calculées
- **HoraireResource** : horaires avec labels jour/type
- **ParoisseResource** : données complètes pour admin

### Utilisation
```php
use App\Http\Resources\AnnonceResource;

return Inertia::render('Admin/Annonces/Index', [
    'annonces' => AnnonceResource::collection($annonces),
]);
```

### Règles
- ❌ Ne jamais retourner un modèle brut à Inertia
- ❌ Ne jamais exposer des données sensibles (passwords, tokens, etc.)
- ✅ Toujours utiliser une Resource

## Scopes Eloquent

### Scopes disponibles

**Paroisse:**
```php
Paroisse::forPublic($slug)->first(); // Sélectionne seulement colonnes publiques
```

**Horaire:**
```php
Horaire::forParoisse($id)->get();           // Filtre par paroisse
Horaire::actifs()->get();                   // Seulement actifs
Horaire::prochaineMesseDe($now)->first();   // Prochaine messe
```

**Annonce:**
```php
Annonce::forParoisse($id)->get();   // Filtre par paroisse
Annonce::publiees()->get();         // Seulement publiées
Annonce::actives()->get();          // Seulement actives
```

### ⚠️ Global Scope BelongsToParoisse

Le trait `BelongsToParoisse` ajoute automatiquement un filtre par `paroisse_id` de l'utilisateur connecté.

**ATTENTION:** Ce scope ne s'applique PAS dans:
- Jobs en file d'attente
- Commandes artisan
- Seeders
- Tests sans authentification

Dans ces cas, utilisez explicitement:
```php
Annonce::forParoisse($paroisseId)->get();
// OU
Annonce::withoutGlobalScope('paroisse')->where('paroisse_id', $paroisseId)->get();
```

## Cache

### Page publique

Chaque page publique est mise en cache avec:
- **Clé:** `paroisse:{id}:public:v1`
- **TTL:** 10 minutes (600 secondes)
- **Driver:** database ou file (pas de Redis requis)

### Invalidation automatique

Le cache est invalidé automatiquement par des **Observers** quand:
- Un horaire est créé/modifié/supprimé
- Une annonce est créée/modifiée/supprimée
- Une paroisse est modifiée

**Pas de code manuel à écrire** - c'est automatique.

### Performance

**Objectif atteint:**
- Page publique à froid: **3 requêtes SQL**
- Page publique à chaud: **0 requête SQL** (cache hit)

## Optimisation des requêtes

### Règles strictes

1. **Toujours spécifier les colonnes** avec `select()`:
```php
// ❌ Mauvais
Annonce::all();

// ✅ Bon
Annonce::select(['id', 'titre', 'contenu', 'publie_le'])->get();
```

2. **Toujours eager load les relations** avec `with()`:
```php
// ❌ Mauvais (N+1)
$annonces = Annonce::all();
foreach ($annonces as $annonce) {
    echo $annonce->paroisse->nom; // Requête à chaque itération
}

// ✅ Bon
$annonces = Annonce::with('paroisse:id,nom')->get();
```

3. **Toujours paginer les listes**:
```php
// Pour admin
$annonces = Annonce::simplePaginate(15);

// Pour public (limite explicite)
$annonces = Annonce::limit(20)->get();
```

4. **Faire les calculs en SQL, pas en PHP**:
```php
// ❌ Mauvais
$total = Annonce::all()->count();

// ✅ Bon
$total = Annonce::count();
```

### Monitoring local

En environnement `local`, le mode strict détecte:
- Lazy loading (N+1)
- Mass assignment non protégé
- Accès à des attributs inexistants

Et les requêtes > 100ms sont loggées automatiquement.

## Rate Limiting

### Login et authentification
- **Login:** 5 tentatives par minute
- **Mot de passe oublié:** 3 tentatives par minute
- **Reset password:** 3 tentatives par minute

### Formulaires publics futurs
Préparer avec `throttle:10,1` (10 soumissions/minute).

## Sécurité

### Headers HTTP
Automatiquement ajoutés par `SecureHeaders` middleware:
- X-Frame-Options: SAMEORIGIN
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Referrer-Policy: strict-origin-when-cross-origin
- Strict-Transport-Security (production uniquement)

### Cookies
En production, les cookies de session sont automatiquement:
- `secure: true` (HTTPS uniquement)
- `httponly: true` (pas accessible en JavaScript)
- `samesite: lax` (protection CSRF)

Configuration dans `config/session.php`.

### Échappement XSS
⚠️ **Ne JAMAIS utiliser `v-html` dans Vue** - risque XSS.

Toujours échapper le contenu utilisateur:
```vue
<!-- ✅ Bon - échappé automatiquement -->
<p>{{ annonce.contenu }}</p>

<!-- ❌ Mauvais - dangereux ! -->
<div v-html="annonce.contenu"></div>
```

## Notifications (structure future)

Classe de base créée pour les futures notifications:
```
app/Notifications/BaseNotification.php
```

### Canaux supportés (gratuits uniquement)
1. **public** : Affichage sur page publique
2. **whatsapp** : Lien vers canal WhatsApp
3. **web_push** : Notifications navigateur (PWA)

⚠️ **Pas de SMS** - trop coûteux.

### Exemple d'utilisation future
```php
class NouvelleAnnonceNotification extends BaseNotification
{
    public function via(): array
    {
        return [
            self::CHANNEL_PUBLIC,
            self::CHANNEL_WHATSAPP,
        ];
    }
    
    public function toPublic(): array
    {
        return [
            'title' => $this->annonce->titre,
            'content' => $this->annonce->contenu,
        ];
    }
}
```

## Commandes de production

### Caches à activer
```bash
# Optimiser l'application
php artisan optimize

# Ou individuellement:
php artisan config:cache   # Cache la config
php artisan route:cache    # Cache les routes
php artisan view:cache     # Cache les vues Blade
php artisan event:cache    # Cache les événements
```

### File d'attente
```bash
# Créer la table jobs
php artisan queue:table
php artisan migrate

# Lancer le worker (supervisor recommandé en prod)
php artisan queue:work --daemon --tries=3 --timeout=90
```

### Opcache PHP
Dans `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.revalidate_freq=0
opcache.validate_timestamps=0  ; En production uniquement
```

## Conventions de code

### Contrôleurs
Les contrôleurs doivent être **fins**:
1. Valider (via Form Request)
2. Autoriser (via Policy)
3. Appeler (une Action si logique complexe)
4. Répondre (via Resource)

```php
public function store(StoreAnnonceRequest $request)
{
    // 1. Validation (automatique via Form Request)
    // 2. Autorisation
    $this->authorize('create', Annonce::class);
    
    // 3. Action
    $annonce = Annonce::create($request->validated());
    
    // 4. Réponse
    return redirect()->route('admin.annonces.index')
        ->with('success', 'Annonce créée.');
}
```

### Nommage
- **Form Requests:** `{Action}{Model}Request` (ex: `StoreAnnonceRequest`)
- **Policies:** `{Model}Policy` (ex: `AnnoncePolicy`)
- **Actions:** Verbe + nom (ex: `CreateParoisseWithAdmin`)
- **Resources:** `{Model}Resource` ou `{Context}{Model}Resource` (ex: `PublicParoisseResource`)
- **Observers:** `{Model}Observer` (ex: `AnnonceObserver`)
- **Scopes:** Verbe au présent (ex: `actifs()`, `publiees()`)

### Tests
Le monitoring en local remplace en partie les tests :
- Mode strict détecte les erreurs courantes
- Log des requêtes lentes
- Pas de N+1 non détecté

Pour les tests formels:
```bash
php artisan test
```

## Checklist nouvelle fonctionnalité

Quand vous ajoutez une nouvelle entité (ex: Membre, Don, Sacrement):

- [ ] Migration avec index composites
- [ ] Modèle avec `HasFactory`, `BelongsToParoisse` si applicable
- [ ] Factory avec données réalistes
- [ ] Form Requests (Store + Update)
- [ ] Policy (viewAny, view, create, update, delete)
- [ ] Action si logique complexe (avec transaction)
- [ ] API Resource (admin + public si nécessaire)
- [ ] Observer si cache à invalider
- [ ] Scopes pour requêtes courantes
- [ ] Tests manuels avec monitoring activé
- [ ] Documentation dans ce fichier

## Dépannage

### "Slow query detected" dans les logs
1. Vérifier que les index existent (voir `DATABASE_PERFORMANCE.md`)
2. Vérifier que `with()` est utilisé pour les relations
3. Vérifier que `select()` limite les colonnes

### "Lazy loading detected" 
Ajouter `with()` pour eager load la relation:
```php
// Avant
$annonces = Annonce::all(); // ❌

// Après
$annonces = Annonce::with('paroisse')->all(); // ✅
```

### Cache ne s'invalide pas
Vérifier que les Observers sont bien enregistrés dans `AppServiceProvider`.

### 403 au lieu de 404
S'assurer de charger le modèle avec `findOrFail()` AVANT d'appeler `authorize()`.

## Ressources

- [Laravel Policies](https://laravel.com/docs/authorization#creating-policies)
- [API Resources](https://laravel.com/docs/eloquent-resources)
- [Query Optimization](https://laravel.com/docs/eloquent#query-scopes)
- [Rate Limiting](https://laravel.com/docs/routing#rate-limiting)
