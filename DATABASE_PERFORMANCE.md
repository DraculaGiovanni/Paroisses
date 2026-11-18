# Documentation - Performance Base de Données

## Index de Performance

### Horaires
- **Index composite** : `(paroisse_id, actif, jour_semaine, heure)`
- **Usage** : Optimise les requêtes publiques qui affichent les horaires actifs d'une paroisse, triés par jour et heure
- **Requête typique** : `WHERE paroisse_id = ? AND actif = 1 ORDER BY jour_semaine, heure`

### Annonces
- **Index 1** : `(paroisse_id, publie_le)` - Pour les annonces publiées
- **Index 2** : `(paroisse_id, epinglee, publie_le)` - Pour les annonces épinglées en priorité
- **Usage** : Optimise l'affichage des annonces avec les épinglées en premier
- **Requête typique** : `WHERE paroisse_id = ? AND publie_le IS NOT NULL ORDER BY epinglee DESC, publie_le DESC`

### Users
- **Index** : `role`
- **Usage** : Optimise les filtres par rôle (admin, secretariat, super_admin)
- **Note** : `paroisse_id` a déjà un index via la clé étrangère

## Monitoring en Développement

En environnement `local`, les outils suivants sont activés automatiquement :

### 1. Mode Strict d'Eloquent
```php
Model::shouldBeStrict();
```
**Détecte :**
- Lazy loading (problème N+1)
- Mass assignment non protégé
- Accès à des attributs inexistants

**Action :** L'application lance une exception au lieu de continuer silencieusement

### 2. Log des requêtes lentes
- Seuil : **> 100ms**
- Fichier : `storage/logs/laravel.log`
- Format : SQL + bindings + temps d'exécution

**Consulter les logs :**
```bash
tail -f storage/logs/laravel.log | grep "Slow query"
```

## Génération de Données de Test

### Seeder de Démonstration
```bash
php artisan migrate:fresh --seed --seeder=DemoDataSeeder
```

**Génère :**
- 3 paroisses (Saint-Michel Cotonou, Notre-Dame Porto-Novo, Sainte-Marie Ouidah)
- ~18 horaires (6 par paroisse)
- ~50 annonces (15-20 par paroisse, mix publiées/brouillons/épinglées)
- 11 utilisateurs (1 super admin, 1 admin + 2-3 secrétariats par paroisse)

**Identifiants de connexion :**
```
Super Admin: super@paroisses.bj / password
Admin 1:     admin0@paroisses.bj / password
Admin 2:     admin1@paroisses.bj / password
Admin 3:     admin2@paroisses.bj / password
```

### Factories Disponibles

**ParoisseFactory**
```php
Paroisse::factory()->create();
```
- Noms réalistes de paroisses béninoises
- Diocèses réels (Cotonou, Porto-Novo, Abomey, Parakou, etc.)
- Adresses et téléphones au format béninois

**HoraireFactory**
```php
Horaire::factory()->create(['paroisse_id' => 1]);
```
- Types : messe, confession, adoration, autre
- Heures réalistes selon le type
- Lieux contextuels (église principale, chapelle, etc.)

**AnnonceFactory**
```php
Annonce::factory()->create(['paroisse_id' => 1]);
Annonce::factory()->draft()->create(['paroisse_id' => 1]); // Brouillon
Annonce::factory()->pinned()->create(['paroisse_id' => 1]); // Épinglée
```
- Titres contextuels (catéchèse, retraite, quête, etc.)
- Contenu en français
- 80% publiées, 15% épinglées

**UserFactory**
```php
User::factory()->create(['paroisse_id' => 1]);
User::factory()->admin()->create(['paroisse_id' => 1]);
User::factory()->superAdmin()->create(); // Pas de paroisse_id
```
- Noms béninois authentiques
- Rôles : secretariat (défaut), admin, super_admin

## Vérification des Index

Pour vérifier qu'un index est utilisé :

```sql
EXPLAIN SELECT * FROM horaires 
WHERE paroisse_id = 1 AND actif = 1 
ORDER BY jour_semaine, heure;
```

Chercher dans le résultat :
- `type: ref` (utilise l'index)
- `key: horaires_paroisse_actif_jour_heure` (nom de l'index)
- `rows: X` (nombre de lignes estimées - devrait être petit)

## Migrations Sécurisées

Toutes les migrations d'index sont :
- ✅ **Non-destructives** : ajoutent seulement, ne suppriment rien
- ✅ **Réversibles** : `migrate:rollback` fonctionne
- ✅ **Sûres en production** : peuvent tourner sur une base avec des données

### Rollback
```bash
# Annuler les 4 dernières migrations (index + colonnes annonces)
php artisan migrate:rollback --step=4
```

## Prochaines Étapes

Pour les futures tables (intentions_messe, quetes, sacrements), penser à :

1. **Ajouter le trait** `BelongsToParoisse` au modèle
2. **Créer les index composites** basés sur les vraies requêtes
3. **Factory** avec données contextuelles en français
4. **Tests** avec le monitoring actif pour détecter les problèmes

### Template d'index pour futures tables
```php
// Exemple pour intentions_messe
Schema::table('intentions_messe', function (Blueprint $table) {
    // Index pour filtrer par paroisse + statut + tri par date
    $table->index(['paroisse_id', 'statut', 'created_at']);
    
    // Index unique pour le code de référence
    $table->unique('code_reference');
});
```
