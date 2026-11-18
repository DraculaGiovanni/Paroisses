# Phase D : Interface Moderne - Plan d'Implémentation

## ✅ Complété

### D1.1 Base de données
- [x] Migration `couleur_principale` ajoutée
- [x] Modèle Paroisse mis à jour
- [x] Migration exécutée

### D1.2 Configuration Tailwind
- [x] Palette personnalisée (cream, spiritual, gold)
- [x] Mode sombre configuré
- [x] Animations custom
- [x] Tokens d'espacement et bordures

### D1.3 Dépendances
- [ ] @vueuse/motion (en cours d'installation)
- [ ] @vueuse/core (en cours d'installation)

## 🚧 À faire

### D2. Composants réutilisables

#### Composants de base
- [ ] `resources/js/Components/UI/Button.vue`
- [ ] `resources/js/Components/UI/Card.vue`
- [ ] `resources/js/Components/UI/Input.vue`
- [ ] `resources/js/Components/UI/Textarea.vue`
- [ ] `resources/js/Components/UI/Select.vue`
- [ ] `resources/js/Components/UI/Badge.vue`

#### Composants avancés
- [ ] `resources/js/Components/UI/Modal.vue`
- [ ] `resources/js/Components/UI/Toast.vue`
- [ ] `resources/js/Components/UI/Skeleton.vue`
- [ ] `resources/js/Components/UI/EmptyState.vue`
- [ ] `resources/js/Components/UI/ConfirmDialog.vue`
- [ ] `resources/js/Components/UI/PageHeader.vue`

#### Composables
- [ ] `resources/js/Composables/useToast.js`
- [ ] `resources/js/Composables/useModal.js`
- [ ] `resources/js/Composables/useConfirm.js`

### D3. Motion Design
- [ ] Configuration transitions Inertia dans `app.js`
- [ ] Plugin Motion pour animations
- [ ] Respect `prefers-reduced-motion`

### D4. Page Publique
- [ ] `resources/js/Pages/Public/Paroisse.vue` (refonte complète)
- [ ] Composant Hero avec nom paroisse
- [ ] Composant NextMassCard avec compte à rebours
- [ ] Composant WeekSchedule (frise horaires)
- [ ] Composant AnnonceCard
- [ ] Variables CSS dynamiques pour couleur d'accent

### D5. Interface Admin
- [ ] `resources/js/Layouts/AdminLayout.vue` (nouveau)
- [ ] `resources/js/Pages/Admin/Dashboard.vue` (nouveau)
- [ ] `resources/js/Pages/Admin/Annonces/Index.vue` (refonte)
- [ ] `resources/js/Pages/Admin/Annonces/Form.vue` (refonte)
- [ ] `resources/js/Pages/Admin/Horaires/Index.vue` (refonte)
- [ ] `resources/js/Pages/Admin/Paroisse/Edit.vue` (nouveau)

### D6. Zone Super Admin
- [ ] `resources/js/Pages/Super/Paroisses/Index.vue`
- [ ] `resources/js/Pages/Super/Paroisses/Create.vue`

### D7. Authentification
- [ ] `resources/js/Pages/Auth/Login.vue` (refonte)
- [ ] `resources/js/Pages/Auth/Register.vue` (refonte)

### D8. Optimisations & Documentation
- [ ] Code splitting configuré
- [ ] Lazy loading images
- [ ] Tests Lighthouse
- [ ] README.md mis à jour
- [ ] PHASE_D_SUMMARY.md avec métriques avant/après

## Structure des dossiers

```
resources/js/
├── Components/
│   ├── UI/              # Composants système de design
│   ├── Admin/           # Composants spécifiques admin
│   ├── Public/          # Composants spécifiques public
│   └── Auth/            # Composants auth
├── Composables/         # Fonctions réutilisables
├── Layouts/             # Layouts de page
└── Pages/               # Pages Inertia
    ├── Admin/
    ├── Super/
    ├── Public/
    └── Auth/
```

## Priorités

1. **Critique** : Composants UI de base + Page publique
2. **Haute** : Interface admin + Dashboard
3. **Moyenne** : Super admin + Auth redesign
4. **Basse** : Optimisations finales

## Notes techniques

- Police : Inter (à télécharger et auto-héberger)
- Animations : CSS + @vueuse/motion uniquement
- Mode sombre : class-based avec toggle
- Accessibilité : WCAG AA minimum
- Performance : Code splitting par route
