<?php

namespace Database\Factories;

use App\Models\Horaire;
use App\Models\Paroisse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Horaire>
 */
class HoraireFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = array_keys(Horaire::TYPES);
        $type = fake()->randomElement($types);

        // Heures réalistes selon le type de célébration
        $heures = match ($type) {
            'messe' => ['06:00', '07:00', '08:00', '17:00', '18:00', '19:00'],
            'confession' => ['16:00', '17:00', '18:00'],
            'adoration' => ['09:00', '14:00', '17:00', '19:00'],
            default => ['09:00', '15:00', '17:00'],
        };

        $lieux = [
            'Église principale',
            'Chapelle Notre-Dame',
            'Chapelle du Saint-Sacrement',
            'Salle paroissiale',
            'Église annexe',
        ];

        $notes = [
            null,
            'En langue fon',
            'En langue yoruba',
            'Pour les jeunes',
            'Pour les enfants',
            'Avec chorale',
        ];

        return [
            'paroisse_id' => Paroisse::factory(),
            'type' => $type,
            'jour_semaine' => fake()->numberBetween(1, 7),
            'heure' => fake()->randomElement($heures),
            'lieu' => fake()->randomElement($lieux),
            'note' => fake()->optional(0.3)->randomElement($notes),
            'actif' => fake()->boolean(90), // 90% actifs
            'heure_fin' => null,
            'valide_du' => null,
            'valide_au' => null,
        ];
    }
}
