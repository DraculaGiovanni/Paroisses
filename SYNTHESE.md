# Synthèse — Phases 1 et 2

## Résultat

- Point de départ vérifié : 227 tests, 823 assertions.
- Résultat final de la phase 1 : 237 tests réussis, 920 assertions.
- Compilation Vue ciblée : 2 tests réussis.
- `npm run build` : réussi, 886 modules transformés.
- Migration SQLite fraîche : réussie.

## Annonces

- Ajout des champs `categorie`, `expire_le`, `archivee_le` et `responsable`.
- Ajout du statut calculé : brouillon, programmée, publiée, expirée ou archivée.
- Liste administrateur filtrable par statut et catégorie, avec pagination et archivage.
- Gestion des urgences : catégorie urgente épinglée automatiquement.
- Publication immédiate ou programmée.
- Page publique historique paginée et page de détail.
- Isolation inter-paroisses vérifiée sur les pages de détail.

## Horaires

- Ajout de `heure_fin`, `valide_du` et `valide_au`.
- Validation des plages de fin et périodes de validité.
- Affichage en plage sur l’administration et le public.
- Filtrage quotidien selon `Africa/Porto-Novo`.
- Prochaine messe gérée en cours ou à venir.

## Calendrier liturgique

- Classe pure `App\Support\LiturgicalCalendar`.
- Algorithme Meeus/Jones/Butcher pour Pâques.
- Périodes, couleurs et changements liturgiques calculés côté serveur.
- Couleurs exceptionnelles stockées par paroisse avec contrainte unique paroisse/date.
- Page administrateur simple pour ajouter ou supprimer une couleur exceptionnelle.
- Permission `liturgie.gerer` ajoutée à la source unique des permissions.

## Cache

- Clé publique portée à `v3`.
- Les horaires et annonces restent des tableaux PHP simples.
- `server_time` et `prochaine_messe` restent calculés à chaque requête.
- Les annonces programmées deviennent visibles après leur heure sans invalidation explicite.
- Le cache est invalidé sur les créations, modifications et suppressions d’annonces et d’horaires.

## Fichiers principaux modifiés

- `app/Support/AnnouncementCategory.php`
- `app/Support/LiturgicalCalendar.php`
- `app/Support/Permission.php`
- `app/Models/Annonce.php`
- `app/Models/Horaire.php`
- `app/Models/CouleurExceptionnelle.php`
- `app/Http/Controllers/PublicParoisseController.php`
- `app/Http/Controllers/Admin/AnnonceController.php`
- `app/Http/Controllers/Admin/HoraireController.php`
- `app/Http/Controllers/Admin/LiturgieController.php`
- `app/Policies/CouleurExceptionnellePolicy.php`
- `app/Http/Requests/Admin/Annonce/StoreAnnonceRequest.php`
- `app/Http/Requests/Admin/Annonce/UpdateAnnonceRequest.php`
- `app/Http/Requests/Admin/Horaire/StoreHoraireRequest.php`
- `app/Http/Requests/Admin/Horaire/UpdateHoraireRequest.php`
- `database/migrations/2026_11_18_094652_add_phase1_fields_to_annonces_table.php`
- `database/migrations/2026_11_18_094706_add_phase1_fields_to_horaires_table.php`
- `database/migrations/2026_11_18_094711_create_couleurs_exceptionnelles_table.php`
- `database/migrations/2026_11_18_095943_add_phase1_permissions_to_existing_secretariats.php`
- `resources/js/Pages/Admin/Annonces/Index.vue`
- `resources/js/Pages/Admin/Annonces/Form.vue`
- `resources/js/Pages/Admin/Horaires/Index.vue`
- `resources/js/Pages/Admin/Liturgie/Index.vue`
- `resources/js/Pages/Public/Paroisse.vue`
- `resources/js/Pages/Public/Annonces/Index.vue`
- `resources/js/Pages/Public/Annonces/Show.vue`
- `tests/Unit/LiturgicalCalendarTest.php`
- `tests/Feature/Phase1PublicAndAdminTest.php`

## Tailles JavaScript

- Avant : application principale 344.16 kB / 119.61 kB gzippé.
- Après : application principale 344.51 kB / 119.71 kB gzippé.
- Page publique avant : 10.61 kB / 3.68 kB gzippé.
- Page publique après : 11.91 kB / 4.09 kB gzippé.
- Hausse gzip de la page publique : 0.41 kB.

## Vérifications manuelles

Ouvrir la console du navigateur et vérifier :

1. `/admin/annonces`
   - créer une annonce urgente ;
   - vérifier qu’elle est épinglée automatiquement ;
   - vérifier les filtres brouillon, programmée, publiée, expirée et archivée ;
   - archiver puis désarchiver une annonce.
2. `/admin/annonces/create`
   - vérifier catégorie, publication immédiate ou programmée, date de fin, responsable et notification.
3. `/admin/horaires`
   - créer une messe avec heure de début, heure de fin et période de validité ;
   - vérifier le refus d’une fin antérieure au début ou d’une période incohérente.
4. `/admin/liturgie`
   - ajouter une couleur exceptionnelle pour aujourd’hui ;
   - vérifier qu’elle apparaît prioritaire sur la page publique.
5. `/p/{slug}`
   - vérifier l’affichage des urgences en tête ;
   - vérifier les plages horaires ;
   - vérifier « en cours jusqu’à HH:MM » ou le compte à rebours ;
   - vérifier le bandeau liturgique avec texte et couleur.
6. `/p/{slug}/annonces`
   - tester le filtre de catégorie ;
   - ouvrir une annonce détaillée ;
   - vérifier qu’une annonce expirée, brouillon, programmée ou archivée reste inaccessible.
7. Créer une annonce comme secrétaire sans `intentions.gerer` ou `liturgie.gerer` si nécessaire, puis tenter `/admin/liturgie` : accès refusé.

## Non vérifié

- La console du navigateur et les pages visuelles n’ont pas été ouvertes par l’assistant.
- Les tests automatisés Vue ne font que compiler les pages demandées ; aucune vérification visuelle réelle n’a été faite.

## Phase 2 — ÉTAPE A (terminée)

### Résultat

- Tests ciblés : 9 tests réussis, 47 assertions (`php artisan test --compact --filter=Phase2IsolationParoissesTest`).
- Suite complète : 246 tests réussis, 967 assertions (237 tests avant cette étape).
- Pint : aucun écart sur les fichiers PHP modifiés.
- Migrations validées par `RefreshDatabase` sur une base SQLite fraîche.
- Périmètre strictement limité à l’ÉTAPE A : aucune route, aucun contrôleur, aucun formulaire Vue, aucune transition de statut, aucun rate limiting, aucune purge, aucun WhatsApp et aucune feuille d’impression.

### Isolation inter-paroisses

- `intentions_messe` et `messages_prives` utilisent le trait `BelongsToParoisse` : scope global en lecture, attribution automatique de `paroisse_id` à la création, refus d’une paroisse étrangère, et `forParoisse()` pour les accès publics.
- Les policies `IntentionMessePolicy` et `MessagePrivePolicy` vérifient la paroisse avant la permission et sont enregistrées dans `AppServiceProvider`.
- Une ressource d’une autre paroisse est introuvable (`find()` null, `findOrFail()` en `ModelNotFoundException`, soit un 404 une fois les contrôleurs ajoutés) et toute action de policy est refusée.

### Permissions

- `intentions.gerer` et `messages.lire` étaient déjà présents dans `App\Support\Permission` (source unique de vérité) ; aucune modification n’était nécessaire.
- Nouveau secrétaire : `annonces.gerer`, `horaires.gerer`, `intentions.gerer` — jamais `messages.lire` par défaut.
- Admin et super_admin disposent de toutes les permissions via `User::hasPermission()`.
- `messages.lire` n’est jamais attribué aux secrétaires existants par la migration de backfill.
- Migration `2026_11_18_105857_add_intentions_gerer_to_existing_secretariats.php` : n'ajoute que `intentions.gerer` aux secrétaires existants ; elle est idempotente et n'introduit aucun doublon avec la migration équivalente de phase 1 `2026_11_18_095943_add_phase1_permissions_to_existing_secretariats.php`.
- `down()` volontairement vide pour cette migration de données : on ne sait pas quels secrétaires avaient déjà `intentions.gerer` avant son exécution ; ne rien faire évite de retirer la permission à tort.

### Données

- `intentions_messe` : `code_suivi` unique de 10 caractères, index `(paroisse_id, statut, created_at)`, relations vers `horaires` (demande et programmation), `users` (`traitee_par`) et `paroisse`.
- `messages_prives` : aucun stockage d’IP, `lu_par` nullable, index `(paroisse_id, statut)` pour les futurs filtres admin.
- Le contenu d’un message ne doit apparaître nulle part ailleurs (page publique, cache, logs, notifications).

### Paiement futur — emplacement prévu (rien implémenté)

Le statut de paiement s’ajoutera :

1. dans une nouvelle migration dédiée sur `intentions_messe`, avec une colonne de statut distincte du statut pastoral (par exemple `non_requis`, `en_attente`, `paye`, `echoue`) et, si le besoin se confirme, une référence de transaction et une date de paiement ;
2. dans une constante séparée de `App\Models\IntentionMesse` — `STATUTS` doit continuer à décrire uniquement `recue`, `confirmee`, `programmee`, `celebree`, `refusee`, `annulee` ;
3. dans la future page de suivi public : afficher au plus le libellé du statut de paiement, jamais de données bancaires ;
4. dans les futures transitions admin : le paiement ne doit pas modifier les transitions pastorales définies au §2.1.

### Fichiers créés

- `database/migrations/2026_11_18_105856_create_intentions_messe_table.php`
- `database/migrations/2026_11_18_105857_add_intentions_gerer_to_existing_secretariats.php`
- `database/migrations/2026_11_18_105858_create_messages_prives_table.php`
- `app/Models/IntentionMesse.php`
- `app/Models/MessagePrive.php`
- `app/Policies/IntentionMessePolicy.php`
- `app/Policies/MessagePrivePolicy.php`
- `tests/Feature/Phase2IsolationParoissesTest.php`

### Fichiers modifiés

- `app/Providers/AppServiceProvider.php`
- `SYNTHESE.md`

## Phase 2 — B1a (terminée)

### Fondations du formulaire public

- Routes publiques `paroisse.intentions.nouvelle` et `paroisse.intentions.store`.
- `App\Support\IntentionCode` : alphabet sans ambiguïté `23456789ABCDEFGHJKMNPQRSTUVWXYZ`, code de 10 caractères généré avec `random_int()`, unicité garantie par `generateUnique()`.
- `App\Support\PublicFormToken` : HMAC-SHA256 avec `config('app.key')`, délai minimal de 3 secondes, expiration après 30 minutes.
- `StoreIntentionRequest` : nom (100), téléphone facultatif (30, format contrôlé), type parmi les cinq valeurs de `IntentionMesse::TYPES`, intention (300), date aujourd’hui ou future en `Africa/Porto-Novo`, horaire limité aux messes actives de la paroisse, honeypot `reference_colis`, jeton signé.
- `PublicIntentionController::create/store/confirmation` : création en statut `recue`, code transmis en flash de session à usage unique, jamais dans l’URL.
- `PublicParoisseResource` expose `slug`, ce qui corrige la génération d’URL Ziggy depuis les pages publiques.

### Fichiers B1a

Créés :

- `app/Support/IntentionCode.php`
- `app/Support/PublicFormToken.php`
- `app/Http/Requests/Public/StoreIntentionRequest.php`
- `app/Http/Controllers/PublicIntentionController.php`

Modifiés :

- `routes/web.php`
- `app/Http/Resources/PublicParoisseResource.php`

## Phase 2 — B1b (terminée)

### Suivi, anti-spam et confidentialité

- Routes `paroisse.intentions.suivi` (GET) et `paroisse.intentions.suivi.rechercher` (POST).
- `SuivreIntentionRequest` : code normalisé, honeypot, jeton signé, messages français.
- Le suivi n’expose que `statut`, `date_programmee`, `heure_programmee` et `motif_refus`. Ni nom, ni téléphone, ni texte de l’intention ne sont renvoyés.
- Un code inconnu et un code d’une autre paroisse produisent le même message neutre : « Aucune demande ne correspond à ce code pour cette paroisse. »
- Rate limiters nommés par IP : 5 demandes par heure et 10 suivis par 10 minutes. Les erreurs sont en français.
- Aucune IP n’est stockée. Le journal local des requêtes lentes ne contient plus les valeurs liées, seulement leur nombre.
- `TrustProxies` est absent : `$request->ip()` utilise `REMOTE_ADDR`. Derrière un proxy, le rate limiting peut être partagé entre plusieurs visiteurs ; `.env` n’a pas été modifié.

### Fichiers B1b

Créés :

- `app/Http/Requests/Public/SuivreIntentionRequest.php`

Modifiés :

- `app/Http/Controllers/PublicIntentionController.php`
- `bootstrap/app.php`
- `app/Providers/AppServiceProvider.php`
- `routes/web.php`

## Phase 2 — B1c (terminée)

### Tests et contrôle final du backend

- 28 tests ciblés réussis, 489 assertions.
- Suite complète à la clôture du backend : 275 tests réussis, 1 467 assertions.
- Pint : aucun écart.
- Contrôle des versions et des modules PHP : aucune dépendance ajoutée, `.env` intact.

### Fichiers B1c

- `tests/Feature/Phase2IntentionsPubliquesTest.php`

## Phase 2 — B2 (terminée)

### Pages publiques des demandes de messe

- `Public/Intentions/Nouvelle` : formulaire complet (nom, téléphone facultatif, type, intention avec compteur de 300 caractères, date souhaitée, messe souhaitée), texte de confidentialité, honeypot et jeton signé.
- `Public/Intentions/Confirmation` : code affiché en grand une seule fois, bouton de copie avec repli sans API presse-papiers, conseil de noter ou de capturer le code, liens vers le suivi et la paroisse.
- `Public/Intentions/Suivi` : formulaire en POST, statut en français, date et heure programmées, motif de refus, message neutre identique pour un code inconnu ou étranger, note de confidentialité.
- La page publique `/p/{slug}` propose désormais deux accès : « Demander une messe » et « Suivre ma demande ».
- Aucun `v-html` dans les trois pages ; les informations personnelles ne sont jamais rendues sur l’écran de suivi.
- Le code de confirmation provient d’un `pull()` de session : il disparaît au second chargement et redirige vers le formulaire.

### Tests de rendu Vue

- Les tests existants dans `tests/Frontend/InputRendering.test.mjs` couvrent déjà les trois pages : compilation SFC et contrôles anti-spam des formulaires.
- Vérifications ajoutées : `reference_colis` avec `autocomplete="off"`, `tabindex="-1"` et `aria-hidden="true"`, champ caché `form_token`, texte brut via `{{ }}`, absence de `v-html`, et confirmation ne rendant le code qu’une seule fois.
- Résultat : 3 tests réussis, 0 échec.

### Résultat B2

- Tests PHP ciblés : 28 tests réussis, 489 assertions.
- Suite complète à la clôture de B2 : 275 tests réussis, 1 467 assertions.
- Suite complète relancée après l’ajout de fichiers hors périmètre : 287 tests réussis, 1 674 assertions, 0 échec.
- `npm run build` : réussi, 894 modules transformés.
- Pint : `--dirty` indisponible sans dépôt Git ; exécution complète réussie, un seul fichier PHP hors B2 (`tests/Feature/Phase2AdminIntentionsTest.php`) reformatté.

### Tailles JavaScript (B2, gzip)

- Application principale : 115.71 kB → 115.83 kB (+0.12 kB).
- Page publique de la paroisse : 3.99 kB → 4.29 kB (+0.30 kB).
- Hausse du JavaScript de la page publique : +0.42 kB gzip, très en dessous de la limite de 10 kB.
- Nouveaux écrans chargés à la demande : Nouvelle 2.34 kB, Confirmation 1.66 kB, Suivi 3.00 kB gzip.
- Feuille de style principale : 11.08 kB → 11.41 kB gzip (+0.33 kB).
- Le build actuel, qui inclut les pages admin ajoutées après B2, affiche : application principale 120.00 kB, page paroisse 4.39 kB, Nouvelle 2.40 kB, Confirmation 1.70 kB, Suivi 3.08 kB, CSS 11.81 kB gzip. Cet écart ne relève pas de B2.

### Import dynamique

- `resources/js/app.js` utilise `resolvePageComponent` avec `import.meta.glob('./Pages/**/*.vue')` sans `eager` : les pages sont bien importées dynamiquement.
- Le build génère des chunks séparés `Nouvelle`, `Confirmation`, `Suivi` et `Paroisse`, confirmant le chargement à la demande.

### Vérifications manuelles demandées par l’utilisateur

1. `/p/{slug}` : vérifier la nouvelle section « Demander une messe » et ses deux boutons sur mobile.
2. `/p/{slug}/intentions/nouvelle` :
   - envoyer un formulaire vide et vérifier les messages en français ;
   - saisir plus de 300 caractères dans l’intention et vérifier la limite ;
   - choisir une date passée : elle doit être refusée ou inaccessible ;
   - vérifier que la liste des messes ne contient que les messes de la paroisse ;
   - envoyer une demande valide et vérifier l’arrivée sur la confirmation.
3. Confirmation :
   - vérifier que le code est lisible en grand et que le bouton « Copier le code » fonctionne ;
   - vérifier que le code n’apparaît pas dans l’URL ;
   - recharger la page : elle doit revenir au formulaire ;
   - noter le code, puis quitter la page.
4. `/p/{slug}/intentions/suivi` :
   - saisir le code en minuscules : le résultat doit s’afficher ;
   - vérifier que seuls le statut, la date, l’heure et le motif de refus apparaissent ;
   - saisir un code inconnu et un code d’une autre paroisse : même message neutre ;
   - enchaîner plus de 10 tentatives : message de limitation en français.
5. Sur téléphone : vérifier les zones tactiles de 44 px, la lisibilité du code et le clavier du champ de suivi.

### Non vérifié en B2

- L’interface n’a pas été ouverte dans un navigateur par l’assistant.
- Les tests Vue compilent les pages et contrôlent la présence des protections ; ils ne remplacent pas un essai réel du parcours.

### Périmètre

- Les fichiers d’administration des intentions de messe et leur test ont été créés après la clôture de B2 ; ils ne font pas partie de cette étape et ne doivent pas être enchaînés ici.

## Phase 2 — Audit de l’administration des intentions (vérification, sans nouvelle fonctionnalité)

### Résultat

- Tests ciblés : 13 tests réussis, 222 assertions (`php artisan test --compact tests/Feature/Phase2AdminIntentionsTest.php`).
- Suite complète : 288 tests réussis, 1 689 assertions, 0 échec.
- Pint : aucun écart (`vendor/bin/pint --format agent`).
- `npm run build` : réussi, 894 modules transformés.
- Aucune modification du code de production : seules 3 confirmations de test ont été ajoutées (détail §« Ajouts de tests »).

### 1. Matrice des transitions autorisées

Source : `App\Models\IntentionMesse::TRANSITIONS` ; contrôle serveur par `Rule::in($intention->transitionsDisponibles())` dans `TransitionIntentionRequest`.

| Statut courant | Transitions autorisées |
| --- | --- |
| `recue` | `confirmee`, `programmee`, `refusee`, `annulee` |
| `confirmee` | `programmee`, `refusee`, `annulee` |
| `programmee` | `celebree`, `annulee` |
| `celebree` | aucune (terminal) |
| `refusee` | aucune (terminal) |
| `annulee` | aucune (terminal) |

Confirmé par tests : chaîne valide `recue → confirmee → programmee → celebree` appliquée ; `celebree → refusee` et `celebree → annulee` refusés ; transition invalide `refusee → confirmee` refusée (`assertSessionHasErrors('statut')` et statut inchangé).

### 2. Permissions

- Secrétaire sans `intentions.gerer` : 403 sur l’index, le détail, la feuille et le PATCH de statut (policy + `authorize()` du FormRequest).
- Admin de la paroisse A sur une demande de la paroisse B : 404 (route model binding filtré par le scope global `BelongsToParoisse`).
- `super_admin` : traité comme prévu — accès confirmé par test sur l’index (2 demandes de deux paroisses visibles) et le détail d’une autre paroisse (200). `IntentionMessePolicy` renvoie `true`, le scope global ne filtre pas et le middleware `paroisse.isolation` le laisse passer.

### 3. Isolation inter-paroisses

- Liste : scope global `BelongsToParoisse` + filtres statut/date.
- Détail : route model binding + policy → 404 pour une demande étrangère.
- Feuille imprimable : scope global, regroupement par messe/date ; test étendu avec une demande programmée d’une autre paroisse absente du rendu.
- Compteur du menu : `ShareInertiaData`, requête `where('paroisse_id', $user->paroisse_id)` et `0` sans permission.

### 4. Lien WhatsApp

- Présent uniquement si un téléphone est renseigné (`whatsapp_url` nul côté contrôleur, `v-if` dans `Show.vue`).
- Numéro nettoyé par `preg_replace('/\D+/', '', ...)` : `+229 01 23 45 67 89` → `2290123456789`.
- Texte encodé avec `rawurlencode()` (nom, code de suivi et paroisse) ; test vérifie le préfixe `https://wa.me/`, le code encodé et l’absence de lien sans téléphone.

### 5. Commande `intentions:purger`

- Critères : statut dans `celebree`, `refusee`, `annulee` **et** `updated_at <= maintenant - 90 jours` (`config('paroisses.intentions.purge_apres_jours')`).
- Action : `nom_demandeur = 'Demandeur anonymisé'`, `telephone = null` ; `type` et `statut` conservés ; idempotente (`updateQuietly` + condition nom/téléphone).
- Laisse intactes : demande célébrée récente, demande `recue` ancienne, demande `programmee` ancienne.
- Scheduler : `Schedule::command('intentions:purger')->dailyAt('03:00')` dans `routes/console.php`.
- Log : uniquement le nombre de demandes anonymisées.

### 6. Logs

- Aucun nom, téléphone ni contenu d’intention journalisés : commande (comptage seul), contrôleur public (`sql_state` seul), `DB::listen` local (SQL + nombre de bindings, jamais les valeurs).
- Test dédié avec `TestHandler` vérifie l’absence des valeurs secrètes après une transition et une purge.

### Ajouts de tests (uniquement, aucun code de production modifié)

- `test_invalid_transitions_are_rejected` : ajout du cas `celebree → annulee` refusé.
- `test_super_admin_can_view_intentions_of_any_parish` : nouveau test.
- `test_printable_sheet_groups_programmed_intentions_by_mass_and_date` : ajout d’une demande programmée d’une autre paroisse, absente du rendu.

### Checklist de vérifications manuelles (à exécuter par l’utilisateur)

1. `/admin/intentions` : filtrer par statut et par date ; vérifier que seules les demandes de la paroisse apparaissent et que le compteur « nouvelles » correspond.
2. Ouvrir une demande : vérifier nom, téléphone, type, texte de l’intention et statut affichés.
3. Enchaîner `recue → confirmee → programmee` (date + messe de la paroisse) puis `celebree` ; vérifier le refus de `celebree → refusee` et `celebree → annulee`.
4. Tenter un refus sans motif (message en français) et avec un motif de plus de 200 caractères (refusé).
5. `/admin/intentions/feuille?date=AAAA-MM-JJ` : vérifier le regroupement par messe et l’absence d’une demande programmée d’une autre paroisse.
6. Sur une demande avec téléphone : vérifier le lien WhatsApp, le numéro nettoyé et le texte encodé dans l’URL ; vérifier son absence sans téléphone.
7. Comme secrétaire sans `intentions.gerer` : 403 sur les 4 endpoints ; comme admin A : 404 sur une demande de B ; comme `super_admin` : accès aux demandes des deux paroisses.
8. Lancer `php artisan intentions:purger` : vérifier le nombre affiché, l’anonymisation ciblée et la conservation de `type`/`statut`.
9. Vérifier `php artisan schedule:list` : `intentions:purger` présent chaque jour à 03:00.
10. Consulter les logs après une transition et une purge : aucun nom, téléphone ni texte d’intention.

### Correctif navigation signalé par l’utilisateur

- Après connexion, l’utilisateur atterrit sur `/dashboard`, qui utilise `AuthenticatedLayout` (layout Breeze). Ce menu ne contenait que Dashboard, Annonces, Horaires et Paramètres : le lien vers les demandes de messe manquait, alors qu’il n’existe que dans `AdminLayout` (pages `/admin/*`).
- Ajout d’un lien « Demandes de messe » vers `route('admin.intentions.index')` dans les deux menus (desktop et mobile) de `resources/js/Layouts/AuthenticatedLayout.vue`, conditionné à `super_admin` ou à la permission `intentions.gerer`. Les liens d’administration sont désormais aussi visibles pour un `super_admin` sans paroisse.
- Aucun contrôleur, test PHP ou code de production modifié. `npm run build` : réussi, 894 modules transformés.
- Données réelles présentes au moment du contrôle : 1 seule demande, rattachée à la paroisse id 4 (admin correspondant : utilisateur id 13). Une demande d’une autre paroisse n’est donc pas visible, conformément à l’isolation.

### Création des secrétaires et retrait du dashboard Breeze

- Constat : `TeamController`, ses FormRequests, les routes et le menu « Équipe » existaient, mais le composant `Admin/Equipe/Index.vue` était absent. Le lien menait donc à une page introuvable : l’admin ne pouvait pas créer de secrétaire.
- Parcours rendu possible : admin → `/admin/equipe` → « Ajouter un membre » (nom, e-mail, rôle *Secrétaire*, permissions cochées ; valeurs par défaut `annonces.gerer`, `horaires.gerer`, `intentions.gerer`). Le mot de passe temporaire est affiché une seule fois après création ou réinitialisation et doit être changé à la première connexion. Le `super_admin` peut en plus choisir la paroisse. Le contrôleur et la source unique des permissions n’ont pas été modifiés.
- Retrait du dashboard Breeze : la route nommée `dashboard` pointe désormais vers `DashboardController@index` (page `Admin/Dashboard`), le composant `resources/js/Pages/Dashboard.vue` est supprimé, et les liens de `AdminLayout.vue` et `Welcome.vue` pointent vers `admin.dashboard`. Les contrôleurs d’authentification et leurs redirections restent inchangés, donc les tests de redirection restent valides.
- Vérifications : Pint sans écart ; `npm run build` réussi (894 modules) ; suite complète 288 tests réussis, 1 689 assertions. Un premier passage a échoué sur `Phase1PublicAndAdminTest::test_public_category_filter_and_detail_isolate_between_paroisses` (cache public figé entre tests), test passé isolément puis suite intégralement verte au second lancement sans modification : instabilité à surveiller, non liée à ces changements.

### Correction du menu et de la déconnexion admin

- Cause du menu incomplet : dans `AdminLayout.vue`, la fonction `navItems` contenait un `return [...]` prématuré. Les blocs `items.push()` suivants (Intentions, Liturgie, Équipe, Réglages) étaient donc inatteignables : seul « Accueil / Annonces / Horaires » s’affichait. Le bloc « Paroisses » du super_admin était lui aussi une variable locale jamais retournée.
- Correction : `const items = [...]` puis tous les `items.push()` avant un `return items` unique ; « Paroisses » est ajouté au tableau pour un `super_admin` sans paroisse.
- Déconnexion : le lien n’existait que dans le menu utilisateur de la barre latérale desktop. Ajout d’un bouton de déconnexion dans l’en-tête mobile (icône + `aria-label`), avec `router.post(route('logout'))`.
- Mobile : barre d’onglets inférieure rendue défilable horizontalement (`overflow-x-auto`, `min-w-max`, libellés `whitespace-nowrap`) pour que les 7 entrées restent accessibles à 360 px.
- `npm run build` : réussi, 894 modules transformés. Aucun test PHP modifié.

### Correction du crash du tableau de bord et de la redirection

- Erreur signalée : `Dashboard.vue:105 TypeError: Cannot read properties of null (reading 'slug')`. Le bouton « Voir le site public » utilisait `$page.props.paroisse.slug` alors que `paroisse` est `null` pour un compte sans paroisse rattachée (super_admin notamment). Le bouton est désormais conditionné par `v-if="$page.props.paroisse"`.
- Redirection après connexion : les contrôleurs `Auth` redirigent maintenant vers `route('admin.dashboard')` (`/admin/dashboard`). L’ancienne URL `/dashboard` conserve une redirection 302 vers le tableau de bord admin pour les favoris ; la page Breeze n’existe plus.
- Tests mis à jour en conséquence (Authentification, vérification d’e-mail, mot de passe forcé, partage Inertia). Le test « admin sans paroisse » utilise désormais `profile.edit`, car le middleware `paroisse.isolation` refuse légitimement l’accès à `/admin/*` à un utilisateur sans paroisse.
- Vérifications : Pint sans écart ; suite complète 288 tests réussis, 1 689 assertions ; `npm run build` réussi, 894 modules transformés.

### Déconnexion et première connexion du secrétaire

- Déconnexion : ajout d’un bouton « Déconnexion » **visible** dans le pied de la barre latérale admin, en plus du menu déroulant du profil et du bouton icône de l’en-tête mobile.
- Blocage du secrétaire : `RequiredPasswordChangeController::edit()` rendait `Auth/ChangePassword`, mais ce composant Vue n’existait pas — le secrétaire restait donc sur un écran vide au premier login (`must_change_password = true`). Page créée : nouveau mot de passe (12 caractères minimum) + confirmation, message en français et lien « Se déconnecter ». Le changement volontaire reste disponible dans **Profil** (`UpdatePasswordForm` existant).
- Tableau de bord : grille de statistiques passée de 4 colonnes dès `md` à 2 colonnes sur mobile puis 4 sur `lg`.
- Vérifications : Pint sans écart ; suite complète 288 tests réussis, 1 689 assertions ; `npm run build` réussi, 895 modules transformés (chunk `ChangePassword` généré).

### Direction visuelle proposée (à valider avant application large)

- Constat : les composants UI existants (crème, spirituel, or, coins arrondis, mode sombre) sont cohérents ; les écarts viennent surtout des grilles trop denses sur tablette et de la barre d’onglets mobile.

1. Palette : fond crème chaud, une seule couleur d’accent par écran, or réservé aux actions et badges ; aucun dégradé.
2. Typographie : 16 px minimum, deux familles maximum, titres en serif humaniste, interligne généreux sur mobile.
3. Cartes : une colonne sous 640 px, deux colonnes à partir de `sm`, trois ou quatre sur grand écran ; espacement par `gap`, zones tactiles de 44 px, ombres légères, dark mode conservé.

Aucune nouvelle dépendance, mouvement CSS uniquement et `prefers-reduced-motion` respecté. En attente de validation de l’utilisateur pour appliquer au-delà des correctifs déjà faits.

### Application de la direction visuelle (validée par l’utilisateur)

- Composants : `Card` n’anime plus que `transform` au survol ; `Button` garantit 44 px de hauteur (`min-h-11`, 48 px en `lg`) ; `Input` et `Select` passent à `min-h-11` ; `PageHeader` titre responsive (24 → 30 px) et actions qui reviennent à la ligne ; `StatCard` aère ses paddings et son chiffre sur mobile.
- Pages : tableau de bord 2 colonnes mobile → 4 sur `lg` ; horaires 1 → 2 (`sm`) → 3 (`xl`) ; listes d’annonces et d’intentions 1 → 2 colonnes sur grand écran ; formulaires en 2 colonnes dès `sm` ; espacements harmonisés par `gap` au lieu de `mb`.
- Poids ajouté : CSS 11,85 → 11,89 kB gzip (+0,04 kB) ; JavaScript principal inchangé (120,02 kB gzip) ; aucune dépendance ajoutée. `prefers-reduced-motion` est déjà couvert globalement dans `resources/css/app.css`.
- `npm run build` : réussi, 895 modules transformés. Suite PHP inchangée : 288 tests réussis, 1 689 assertions.
- Contrôles à faire par l’utilisateur sur navigateur et téléphone : dashboard à 360 px (2 cartes par ligne, pas de débordement), listes d’annonces/horaires/intentions à 360 / 768 / 1280 px, boutons et champs d’au moins 44 px, actions du `PageHeader` qui ne se chevauchent pas, mode sombre, aperçu d’impression de la feuille.

### Non vérifié

- L’interface n’a pas été ouverte dans un navigateur par l’assistant ; la checklist ci-dessus reste à exécuter par l’utilisateur.
