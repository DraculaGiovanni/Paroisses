<?php

namespace Database\Factories;

use App\Models\Activite;
use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activite>
 */
class ActiviteFactory extends Factory
{
    protected $model = Activite::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'paroisse_id' => Paroisse::factory(),
            'user_id' => User::factory(),
            'action' => fake()->randomElement(array_keys(Activite::ACTIONS)),
            'cible_type' => null,
            'cible_id' => null,
            'created_at' => fake()->dateTimeBetween('-30 days'),
        ];
    }
}
