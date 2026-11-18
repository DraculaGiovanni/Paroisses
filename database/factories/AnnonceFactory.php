<?php

namespace Database\Factories;

use App\Models\Annonce;
use App\Models\Paroisse;
use App\Support\AnnouncementCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Annonce>
 */
class AnnonceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titres = [
            'Catéchèse pour adultes',
            'Retraite spirituelle ce weekend',
            'Quête spéciale pour les travaux',
            'Formation des servants de messe',
            'Réunion du Conseil paroissial',
            'Pèlerinage à Dassa-Zoumé',
            'Journée de prière pour la paix',
            'Baptêmes du mois',
            'Préparation au mariage',
            'Visite pastorale de Monseigneur',
            'Fête patronale de la paroisse',
            'Collecte pour les nécessiteux',
            'Répétition de la chorale',
            'Adoration eucharistique nocturne',
            'Réunion des mouvements d\'action catholique',
        ];

        $isPublished = fake()->boolean(80); // 80% publiées

        return [
            'paroisse_id' => Paroisse::factory(),
            'titre' => fake()->randomElement($titres),
            'contenu' => fake()->paragraphs(fake()->numberBetween(2, 4), true),
            'publie_le' => $isPublished
                ? fake()->dateTimeBetween('-30 days', '+7 days')
                : null,
            'epinglee' => $isPublished ? fake()->boolean(15) : false, // 15% épinglées
            'notifier' => fake()->boolean(30),
            'date_debut' => fake()->optional(0.5)->dateTimeBetween('now', '+30 days'),
            'date_fin' => fake()->optional(0.3)->dateTimeBetween('+31 days', '+60 days'),
            'actif' => fake()->boolean(90),
            'categorie' => fake()->randomElement(AnnouncementCategory::values()),
            'expire_le' => fake()->optional(0.25)->dateTimeBetween('+1 day', '+60 days'),
            'archivee_le' => null,
            'responsable' => fake()->optional(0.2)->name(),
        ];
    }

    /**
     * Indicate that the annonce is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'publie_le' => null,
            'epinglee' => false,
        ]);
    }

    /**
     * Indicate that the annonce is pinned.
     */
    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'epinglee' => true,
            'publie_le' => $attributes['publie_le'] ?? now(),
        ]);
    }
}
