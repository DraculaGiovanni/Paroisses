<?php

namespace Database\Factories;

use App\Models\Avis;
use App\Models\Paroisse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Avis>
 */
class AvisFactory extends Factory
{
    protected $model = Avis::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'paroisse_id' => Paroisse::factory(),
            'message' => fake()->sentence(12),
            'statut' => 'en_attente',
            'modere_le' => null,
        ];
    }

    public function publie(): static
    {
        return $this->state(fn (): array => [
            'statut' => 'publie',
            'modere_le' => now(),
        ]);
    }
}
