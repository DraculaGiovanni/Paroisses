<?php

namespace Database\Factories;

use App\Models\Paroisse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Paroisse>
 */
class ParoisseFactory extends Factory
{
    /**
     * Noms de paroisses catholiques du Bénin
     */
    private static array $paroisses = [
        ['nom' => 'Saint-Michel de Cotonou', 'diocese' => 'Cotonou'],
        ['nom' => 'Notre-Dame de Porto-Novo', 'diocese' => 'Porto-Novo'],
        ['nom' => 'Sainte-Marie de Ouidah', 'diocese' => 'Cotonou'],
        ['nom' => 'Saint-Jean-Baptiste de Abomey', 'diocese' => 'Abomey'],
        ['nom' => 'Sacré-Cœur de Parakou', 'diocese' => 'Parakou'],
        ['nom' => 'Saint-Joseph de Bohicon', 'diocese' => 'Abomey'],
        ['nom' => 'Notre-Dame de Lourdes de Lokossa', 'diocese' => 'Lokossa'],
        ['nom' => 'Saint-Paul de Djougou', 'diocese' => 'Djougou'],
        ['nom' => 'Sainte-Thérèse de Natitingou', 'diocese' => 'Natitingou'],
        ['nom' => 'Saint-Pierre de Kandi', 'diocese' => 'Parakou'],
    ];

    private static int $index = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Utilise les noms prédéfinis de manière séquentielle
        $paroisse = self::$paroisses[self::$index % count(self::$paroisses)];
        self::$index++;

        $nom = $paroisse['nom'];

        return [
            'nom' => $nom,
            'slug' => Str::slug($nom).'-'.fake()->unique()->numberBetween(1, 999),
            'diocese' => $paroisse['diocese'],
            'adresse' => fake()->optional(0.8)->streetAddress().', '.fake()->city(),
            'telephone' => fake()->optional(0.9)->regexify('\+229 [0-9]{2} [0-9]{2} [0-9]{2} [0-9]{2}'),
            'email' => fake()->optional(0.7)->safeEmail(),
            'whatsapp_channel_url' => fake()->optional(0.3)->url(),
            'logo_path' => null,
        ];
    }
}
