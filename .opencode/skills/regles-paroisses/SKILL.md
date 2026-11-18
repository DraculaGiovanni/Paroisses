---
name: regles-paroisses
description: Contexte et règles impératives du projet paroisses (multi-tenant Laravel/Inertia/Vue, mobile d'abord, Bénin). À charger avant toute implémentation de fonctionnalité, migration, formulaire public ou modification de tests.
---

# Règles du projet paroisses

## Contexte
Plateforme multi-paroisses catholiques du Bénin, gratuite. super_admin crée les paroisses et leur admin ; l'admin crée les secrétaires avec des permissions ; les paroissiens n'ont pas de compte (page publique /p/{slug}). Pas de SMS. Fuseau Africa/Porto-Novo. Interface en français, mobile d'abord (petits Android, données limitées).

## Interdits
- Désactiver le scope BelongsToParoisse ou Model::shouldBeStrict() pour faire passer un test.
- Supprimer ou affaiblir un test. Renommer une route existante.
- Ajouter une dépendance non autorisée par la phase en cours. Aucune bibliothèque JavaScript nouvelle.
- Toucher au fichier .env : ajouter les variables à .env.example (valeurs vides) et donner les commandes.
- Écrire dans les logs des messages, noms ou téléphones. Stocker une IP en base.

## Données et cache
- Aucun SQL propre à un moteur (pas de NOW(), pas de JSON MySQL). Pour indexer du texte long, colonne de hachage sha256.
- Cache : tableaux simples et scalaires uniquement, jamais de modèles ni de JsonResource. Aucune donnée dépendante du temps figée : le cache garde les dates, le filtrage « visible maintenant » se fait à chaque requête en PHP. Clé versionnée, invalidée à chaque modification.
- Tout nouveau modèle lié à une paroisse : trait BelongsToParoisse, Policy, tests d'isolation entre paroisses.

## Formulaires publics
Rate limiting nommé par IP, honeypot invisible, délai minimal (token horodaté signé), longueurs maximales, texte brut (jamais v-html), erreurs en français.

## Permissions
Source unique de vérité. L'admin a tout. Nouveau secrétaire : annonces.gerer, horaires.gerer, intentions.gerer ; jamais messages.lire par défaut.

## Poids
JS de la page publique : +10 Ko gzip maximum sans justification. Code PWA et formulaires en import dynamique. Donner les tailles avant/après.

## Méthode et honnêteté
- Ne pas relire tout le projet : lire AGENTS.md, SYNTHESE.md, puis seulement les fichiers voisins de ce qui est créé ou modifié.
- Pendant le travail, lancer les tests ciblés (php artisan test --filter=...). Suite complète seulement en fin d'étape.
- Ne jamais dire avoir vérifié l'interface : l'utilisateur la teste.
- Ne rien déclarer terminé sans php artisan test (nombre exact) et npm run build. Signaler toute commande non lancée ou échouée.
- Un seul fichier de synthèse : SYNTHESE.md, mis à jour à chaque étape.