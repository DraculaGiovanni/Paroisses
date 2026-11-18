---
name: motion-css-first
description: Règles d'animation légères pour la page publique et l'admin de la plateforme des paroisses (CSS d'abord, mobile, accessibilité). À charger pour toute animation, transition ou effet de mouvement.
---

# Motion, CSS d'abord

## Principe
Le mouvement sert à guider l'œil et à confirmer une action, jamais à décorer. Les utilisateurs ont de petits Android et des données limitées : chaque animation doit être gratuite en poids et fluide.

## Interdit
- Aucune nouvelle bibliothèque JavaScript d'animation (pas de GSAP, Framer Motion, Lottie, Three.js, anime.js).
- Aucune vidéo ou GIF animé décoratif.
- Aucun clignotement, aucun défilement automatique, aucun carrousel qui tourne seul.

## Techniques autorisées, dans cet ordre
1. Transitions CSS et @keyframes.
2. Transitions de Vue (<Transition>, <TransitionGroup>) déjà incluses dans Vue.
3. IntersectionObserver natif pour révéler un élément quand il apparaît à l'écran.
4. View Transitions et animations liées au scroll en CSS, uniquement avec un repli propre si le navigateur ne les gère pas.

## Règles techniques
- Animer seulement transform et opacity. Jamais width, height, top, left, margin.
- Durées : 150 à 300 ms pour les interactions, 400 à 600 ms maximum pour une entrée de page.
- Courbes douces (ease-out pour entrer, ease-in pour sortir). Pas de rebond exagéré.
- Décalage en cascade (stagger) de 40 à 80 ms entre éléments, 6 éléments maximum.
- will-change uniquement ponctuel, jamais appliqué partout.
- Tout mouvement doit être désactivable :
  @media (prefers-reduced-motion: reduce) { réduire à une simple apparition en fondu ou supprimer }.
- Le contenu doit rester lisible et utilisable si l'animation ne se lance pas.

## Quand animer
- Entrée de la page publique : léger fondu et montée de quelques pixels, une seule fois.
- Retour visuel d'un bouton (appui, chargement, succès).
- Apparition des annonces dans la liste, en cascade courte.
- Changement de période liturgique : transition douce de la couleur du bandeau.
- Ouverture et fermeture de panneaux, menus et boîtes de dialogue.

## Contrôle avant de terminer
- Donner le poids ajouté (CSS et JS) et confirmer qu'aucune dépendance n'a été ajoutée.
- Confirmer la présence de prefers-reduced-motion.
- Dire clairement que le rendu n'a pas été vérifié dans un navigateur : c'est à l'utilisateur de le tester sur téléphone.