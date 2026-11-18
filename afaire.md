# À faire — état du projet Paroisses

Date : 18 novembre 2026

## Ce qui est fait

### Audit de l'administration des intentions (sans nouvelle fonctionnalité)

- Matrice des transitions documentée : `recue → confirmee / programmee / refusee / annulee`, `confirmee → programmee / refusee / annulee`, `programmee → celebree / annulee`, `celebree / refusee / annulee` terminaux. Confirmé par tests : une demande célébrée ne peut plus être refusée ni annulée, une transition invalide est refusée.
- Permissions vérifiées : secrétaire sans `intentions.gerer` → 403, admin A → 404 sur une demande de B, `super_admin` → accès total (test dédié ajouté).
- Isolation vérifiée sur liste, détail, feuille imprimable et compteur du menu.
- Lien WhatsApp : uniquement si téléphone présent, numéro nettoyé, texte encodé.
- Commande `intentions:purger` : anonymise nom/téléphone des demandes célébrées/refusées/annulées de plus de 90 jours, conserve type et statut, inscrite au scheduler à 03:00.
- Aucun nom, téléphone ni contenu d'intention dans les logs.
- Tests ciblés : **13 tests / 222 assertions**.

### Corrections fonctionnelles

- Lien « Demandes de messe » ajouté au menu Breeze, puis tableau de bord Breeze supprimé : `/dashboard` redirige vers `/admin/dashboard` et les contrôleurs d'authentification y redirigent directement.
- `AdminLayout` : `return` prématuré corrigé dans `navItems` (tous les menus s'affichent désormais : Accueil, Annonces, Horaires, Demandes de messe, Liturgie, Équipe, Réglages, + Paroisses pour le super_admin sans paroisse).
- Déconnexion rendue visible : bouton en bas de la barre latérale + icône dans l'en-tête mobile + menu déroulant du profil. Barre d'onglets mobile défilable à 360 px.
- Crash du tableau de bord corrigé : `$page.props.paroisse.slug` protégé par `v-if` quand aucun paroisse n'est rattachée.
- Page **Équipe** créée (`Admin/Equipe/Index.vue`) : le contrôleur existait mais le composant manquait — l'admin peut maintenant créer un secrétaire, choisir ses permissions, réinitialiser son mot de passe et l'activer/désactiver.
- Page **Changement de mot de passe** créée (`Auth/ChangePassword.vue`) : elle manquait, ce qui bloquait la première connexion d'un secrétaire (`must_change_password`). Le changement volontaire reste disponible dans Profil.

### Interface et style (direction validée)

- Palette crème / spirituel / or conservée, aucune nouvelle dépendance, aucun dégradé.
- Cartes : 1 colonne sur mobile, 2 à partir de `sm`, 3–4 sur grand écran ; espacements par `gap`.
- Zones tactiles d'au moins 44 px (boutons, champs, selects) ; titres et actions responsives ; survol des cartes limité à `transform`.
- `prefers-reduced-motion` déjà couvert globalement. Coût : CSS 11,85 → 11,89 kB gzip (+0,04 kB).

### Qualité et livraison

- Suite complète : **288 tests réussis, 1 689 assertions**, 0 échec. Pint sans écart. `npm run build` réussi (895 modules).
- Dépôt Git initialisé et poussé sur `https://github.com/DraculaGiovanni/Paroisses.git` :
  - `e73e89c` — first commit (README.md),
  - `b9f61af` — ajout de l'ensemble du projet (360 fichiers, 46 296 lignes).
- `.env`, `vendor/`, `node_modules/`, `public/build/` et clés de `storage/` exclus par `.gitignore` ; aucun secret détecté.
- Historique détaillé dans `SYNTHESE.md`.

## Ce qui reste à faire

### Vérifications manuelles (à faire par toi dans le navigateur — je ne peux pas les faire)

1. Se connecter en admin → atterrissage sur `/admin/dashboard`, tous les menus visibles, bouton de déconnexion visible (desktop et mobile).
2. `/admin/intentions` : liste, compteur du menu, détail, transition `Programmée` puis `Célébrée`, bouton « Refuser » absent.
3. Lien WhatsApp : présent avec téléphone, absent sans ; numéro nettoyé et message prérempli.
4. `/admin/intentions/feuille?date=…` : aperçu avant impression, mise en page lisible.
5. Créer un secrétaire depuis `/admin/equipe` → mot de passe temporaire affiché une fois → connexion du secrétaire → changement de mot de passe → accès à son tableau de bord.
6. Tester les grilles et boutons à 360 px, 768 px et 1280 px, en mode clair et sombre.
7. Vérifier `php artisan schedule:list` contient `intentions:purger` chaque jour à 03:00.

### Points techniques à surveiller

- Test instable : `Phase1PublicAndAdminTest::test_public_category_filter_and_detail_isolate_between_paroisses` a échoué une fois sur un cache public figé entre tests, puis est repassé vert. À stabiliser (isolation/nettoyage du cache dans les tests) si le problème se reproduit.
- Aucun test navigateur réel (`laravel/dusk` non installé) : les contrôles visuels ci-dessus ne sont pas automatisés.
- Identité Git auto-détectée (`Joseph DJEGUEDE <joseph.djeguede@chem.loc>`) ; à configurer si tu veux un autre auteur pour les prochains commits.
- Déploiement non effectué (aucun environnement de production configuré dans cette session).

### Décision à prendre

- Le mot de passe temporaire impose **actuellement** un changement à la première connexion (`must_change_password = true`). Tu as évoqué « changer son mot de passe si elle le désire » : préciser si tu veux conserver l'obligation (sécurité) ou la rendre facultative.

## Commandes utiles

```bash
php artisan test --compact
vendor/bin/pint --format agent
npm run build
git status -sb
git push origin main
```
