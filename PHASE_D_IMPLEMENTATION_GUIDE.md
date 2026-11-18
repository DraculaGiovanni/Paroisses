# Phase D : Guide d'Implémentation Complet

## État actuel

✅ **Complété** :
- Migration `couleur_principale` créée et exécutée
- Configuration Tailwind avec palette personnalisée
- Composants Button et Card créés
- @vueuse/motion en cours d'installation

## Suite de l'implémentation

### Étape 1 : Finaliser l'installation des dépendances

```bash
# Si npm install a timeout, réessayer
npm install @vueuse/motion @vueuse/core --legacy-peer-deps

# Installer Inter font (auto-hébergée)
npm install @fontsource/inter
```

### Étape 2 : Créer les composants UI manquants

Les fichiers à créer sont listés dans `PHASE_D_PLAN.md`. Voici les priorités :

**Priorité HAUTE** (essentiels pour fonctionner) :
1. `Input.vue`, `Textarea.vue`, `Select.vue` - formulaires
2. `Modal.vue` - dialogues
3. `Toast.vue` + `useToast.js` - notifications
4. `PageHeader.vue` - en-têtes de page

**Priorité MOYENNE** :
5. `Badge.vue` - statuts
6. `Skeleton.vue` - chargement
7. `EmptyState.vue` - états vides
8. `ConfirmDialog.vue` + `useConfirm.js` - confirmations

### Étape 3 : Configurer Motion et Inertia

Dans `resources/js/app.js`, ajouter :

```javascript
import { MotionPlugin } from '@vueuse/motion'
import { router } from '@inertiajs/vue3'

// Configurer Motion
app.use(MotionPlugin, {
  directives: {
    'fade': {
      initial: { opacity: 0 },
      enter: { opacity: 1, transition: { duration: 300 } },
    },
  },
})

// Progress bar Inertia
router.on('start', () => {
  // Afficher progress bar
})

router.on('finish', () => {
  // Masquer progress bar
})
```

### Étape 4 : Refaire la page publique

Priorité #1 car c'est la page la plus consultée.

**Fichier** : `resources/js/Pages/Public/Paroisse.vue`

Structure suggérée :
- Hero avec nom + couleur personnalisée
- Carte "Prochaine messe" avec compte à rebours
- Grille d'horaires par jour
- Liste d'annonces (épinglées en premier)

**Controller** : Ajouter la couleur dans `PublicParoisseController` :

```php
public function show(Paroisse $paroisse)
{
    $data = Cache::remember(
        "paroisse:{$paroisse->id}:public:v1",
        600,
        function () use ($paroisse) {
            // ... existant ...
            
            return [
                'paroisse' => PublicParoisseResource::make($paroisse),
                'couleur_principale' => $paroisse->couleur_principale ?? '#1E40AF',
                // ... reste
            ];
        }
    );
    
    return Inertia::render('Public/Paroisse', $data);
}
```

### Étape 5 : Interface Admin

**Layout Admin** : `resources/js/Layouts/AdminLayout.vue`
- Sidebar avec menu
- Header avec user dropdown
- Main content area

**Dashboard** : `resources/js/Pages/Admin/Dashboard.vue`
- Compteurs (annonces, horaires)
- Raccourcis rapides
- Activité récente

### Étape 6 : Tests et optimisations

```bash
# Vérifier que les tests passent toujours
php artisan test

# Build production
npm run build

# Vérifier la taille des bundles
ls -lh public/build/assets/

# Lighthouse audit
# Ouvrir en mode incognito et lancer Lighthouse
```

## Commandes utiles

```bash
# Développement
npm run dev

# Build production
npm run build

# Tests
php artisan test
php artisan test --filter=PerformanceTest

# Migrations
php artisan migrate:fresh --seed

# Clear cache
php artisan optimize:clear
```

## Checklist finale

- [ ] Tous les tests passent (163/163)
- [ ] Build production réussit sans warnings
- [ ] Page publique < 100KB JS initial
- [ ] Lighthouse Performance > 90
- [ ] Lighthouse Accessibility > 95
- [ ] Toutes les animations respectent `prefers-reduced-motion`
- [ ] Mode sombre fonctionne
- [ ] Navigation clavier complète
- [ ] README.md à jour

## Fichiers clés modifiés

**Backend** :
- `database/migrations/*_add_couleur_principale_to_paroisses_table.php`
- `app/Models/Paroisse.php`
- `app/Http/Controllers/PublicParoisseController.php`
- `app/Http/Resources/PublicParoisseResource.php`

**Frontend** :
- `tailwind.config.js`
- `resources/js/app.js`
- `resources/js/Components/UI/*.vue`
- `resources/js/Pages/**/*.vue`
- `resources/js/Layouts/**/*.vue`

**Documentation** :
- `README.md`
- `PHASE_D_SUMMARY.md`

## Ressources

- [Tailwind CSS](https://tailwindcss.com/docs)
- [VueUse Motion](https://motion.vueuse.org/)
- [Inertia.js](https://inertiajs.com/)
- [Inter Font](https://rsms.me/inter/)

## Support

Si blocage, vérifier :
1. Node modules installés
2. Build assets compilés
3. Cache Laravel vidé
4. Migrations exécutées
