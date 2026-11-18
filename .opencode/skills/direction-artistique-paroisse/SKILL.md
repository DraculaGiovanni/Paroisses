---
name: direction-artistique-paroisse
description: Identité visuelle et principes de design de la plateforme des paroisses catholiques du Bénin. À charger pour tout travail de design, de mise en page, de typographie, de couleurs ou de composants visuels.
---

# Direction artistique, plateforme des paroisses

## Intention
Un site chaleureux, sobre et digne, qui donne envie de pousser la porte de la paroisse. Il doit sembler fait pour cette communauté, pas pour une entreprise.

## À éviter
- L'aspect « modèle générique » : dégradés violets, cartes toutes identiques, ombres lourdes, emojis partout.
- Le ton froid de logiciel d'entreprise.
- Les photos d'illustration génériques quand une vraie photo de la paroisse est possible.
- Surcharge : une idée visuelle forte par écran.

## Couleurs
- La couleur liturgique du jour est l'accent principal de la page (violet, vert, blanc, rouge, rose).
- Le fond reste neutre et clair, chaud plutôt que blanc pur.
- Contraste conforme à WCAG AA (utiliser le ColorContrast existant).
- La couleur n'est jamais seule : toujours accompagnée d'un texte ou d'un libellé.

## Typographie
- Une police de titre avec du caractère (sérif humaniste ou à empattements doux) et une police de texte très lisible.
- Limiter à deux familles et trois graisses. Charger seulement les graisses utilisées, avec font-display: swap.
- Texte de base 16 px minimum, interligne confortable, lignes courtes sur mobile.

## Composition
- Mobile d'abord : une colonne, zones tactiles de 44 px minimum, information essentielle visible sans défiler (prochaine messe, période du jour).
- Hiérarchie nette : un titre fort, un contenu respirant, de grands espaces.
- Les horaires et la prochaine messe sont les éléments les plus visibles.
- Cohérence : réutiliser les 12 composants UI existants avant d'en créer un nouveau.

## Ton et contenu
- Français simple, chaleureux, respectueux. Pas de jargon technique.
- Messages d'erreur doux et clairs.

## Lisibilité terrain
- Lisible en plein soleil sur écran de téléphone modeste : contraste élevé.
- Images légères (formats modernes, taille adaptée, chargement différé).
- Fonctionne avec une connexion lente.

## Méthode de travail
1. Proposer d'abord une direction (palette, typographie, trois principes visuels) et attendre la validation.
2. Implémenter ensuite avec les composants et le design system existants.
3. Terminer par : poids ajouté, contraste vérifié, et une liste de contrôles que l'utilisateur doit faire lui-même sur navigateur et téléphone. Ne jamais dire avoir vérifié l'interface visuellement.