# Sécurité et Isolation des Paroisses

Ce document explique comment sécuriser l'application pour que chaque paroisse ne puisse accéder qu'à ses propres données.

## Hiérarchie des Rôles

1. **Super Admin** (`super_admin`)
   - Accès à toutes les paroisses
   - Peut créer et gérer les paroisses
   - N'est pas rattaché à une paroisse spécifique

2. **Admin** (`admin`)
   - Accès uniquement à sa paroisse
   - Peut modifier les paramètres de sa paroisse
   - Peut gérer les utilisateurs de sa paroisse

3. **Secrétariat** (`secretariat`)
   - Accès uniquement à sa paroisse
   - Accès en lecture/écriture aux données de sa paroisse

## Middleware d'Isolation

### `EnsureUserBelongsToParoisse`

Ce middleware vérifie automatiquement que l'utilisateur ne peut accéder qu'aux ressources de sa propre paroisse.

**Utilisation dans les routes :**

```php
Route::middleware(['auth', 'paroisse.isolation'])->group(function () {
    // Toutes les routes ici sont protégées
});
```

### Comment ça fonctionne ?

1. Les super_admin ont accès à tout
2. Les admin et secrétariat doivent avoir une `paroisse_id`
3. Si un modèle dans les paramètres de route a une `paroisse_id` différente, l'accès est refusé avec une erreur 403

## Trait `BelongsToParoisse`

Pour les modèles qui doivent être isolés par paroisse, utilisez le trait `BelongsToParoisse`.

### Fonctionnalités du trait :

1. **Global Scope automatique** : Filtre toutes les requêtes pour ne retourner que les données de la paroisse de l'utilisateur
2. **Auto-remplissage** : Remplit automatiquement `paroisse_id` lors de la création
3. **Relation Eloquent** : Définit la relation `belongsTo(Paroisse::class)`

### Exemple d'utilisation :

```php
<?php

namespace App\Models;

use App\Models\Concerns\BelongsToParoisse;
use Illuminate\Database\Eloquent\Model;

class Membre extends Model
{
    use BelongsToParoisse;

    protected $fillable = ['nom', 'prenom', 'email', 'paroisse_id'];
}
```

### Migration correspondante :

```php
Schema::create('membres', function (Blueprint $table) {
    $table->id();
    $table->string('nom');
    $table->string('prenom');
    $table->string('email');
    $table->foreignId('paroisse_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
});
```

### Désactiver temporairement le scope

Si vous devez accéder à toutes les données (par exemple en tant que super_admin) :

```php
// Dans un contrôleur super admin
$tousMembres = Membre::withoutGlobalScope('paroisse')->get();
```

## Policy `ParoissePolicy`

La policy contrôle les permissions sur le modèle Paroisse :

- **viewAny** : Seul le super_admin peut voir toutes les paroisses
- **view** : Super_admin ou utilisateur de cette paroisse
- **create** : Seul le super_admin
- **update** : Super_admin ou admin de cette paroisse
- **delete** : Seul le super_admin

### Utilisation dans les contrôleurs :

```php
public function update(Request $request, Paroisse $paroisse)
{
    $this->authorize('update', $paroisse);
    
    // Le reste du code...
}
```

## Checklist pour Nouveaux Modèles

Quand vous créez un nouveau modèle qui doit être isolé par paroisse :

- [ ] Ajouter la colonne `paroisse_id` dans la migration
- [ ] Ajouter `use BelongsToParoisse;` dans le modèle
- [ ] Ajouter `'paroisse_id'` dans le tableau `$fillable`
- [ ] Appliquer le middleware `paroisse.isolation` sur les routes
- [ ] Créer une Policy si nécessaire

## Tests de Sécurité

Pour tester l'isolation :

1. Créez deux paroisses différentes avec des admins différents
2. Connectez-vous avec l'admin de la paroisse A
3. Essayez d'accéder à une ressource de la paroisse B
4. Vous devriez recevoir une erreur 403

## Routes Protégées

Actuellement protégées par le middleware :

- `/admin/*` - Toutes les routes admin (avec `paroisse.isolation`)
- `/super/*` - Routes super admin (avec `can:manage-paroisses`)
